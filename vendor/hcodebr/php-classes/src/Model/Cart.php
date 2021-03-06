<?php 

namespace Hcode\Model; // local onde vc está
use \Hcode\DB\Sql; // local do objeto que vc quer usar 
use \Hcode\Model;  // local do objeto que vc quer usar 
use \Hcode\Model\Product;

class Cart extends Model {

    const SESSION = "Cart";
	const SESSION_ERROR = "CartError";

    public static function getFromSession(){
        $cart = new Cart();
        
       

        if(isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0){
            $cart->get((int)$_SESSION[Cart::SESSION]['idcart']);

            if(!(int)$_SESSION[Cart::SESSION]['iduser'] > 0){
                $user = User::getFromSession();

                $data['iduser'] = $user->getiduser();
                $cart->setData($data);

                //var_dump($cart);
            }

            
            //var_dump($cart);
            //exit;
            
        }else {
            
            
            $cart->getFromSessionID();

            

            if(!(int)$cart->getidcart() > 0 ){

                session_regenerate_id();
                $data = [
                    //'dessessionid' => session_id()
                    'dessessionid' => session_id()
                ];

               

                //var_dump(User::checkLogin(false));
                //exit;

                if(User::checkLogin(false)){
                    $user = User::getFromSession();

                    $data['iduser'] = $user->getiduser();
                }

                

                $cart->setData($data);
               
                $cart->save();
                $cart->setToSession();
            }
        }

        return $cart;
    }

    public function setToSession(){
        // coloca os dados na sessão
        $_SESSION[Cart::SESSION] = $this->getValues();
    }

    public function getFromSessionID(){
        $sql = new Sql();

        $results = $sql->select("SELECT t1.* 
                                 FROM tb_carts t1 
                                 WHERE  exists ( select 1 from tb_orders t2 
                                                 WHERE t1.dessessionid = :dessessionid 
                                                 and   USING(idcart) ", [
                                 ':dessessionid' => session_id()
                                ]);

        if(count($results) > 0 ){
            $this->setData($results[0]);    
        }
    }

    public function get(int $idcart){
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart", [
            ':idcart' => $idcart
        ]);

        if(count($results) > 0 ){
            $this->setData($results[0]);    
        }
    }

    public function save()
    {
        $sql = new Sql();
        $results = 
        $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", [
            ':idcart'=>$this->getidcart(),
            ':dessessionid'=>$this->getdessessionid(),
            ':iduser'=>$this->getiduser(),
            ':deszipcode'=>$this->getdeszipcode(),
            ':vlfreight'=>$this->getvlfreight(),
            ':nrdays'=>$this->getnrdays()
        ]);

        //var_dump($this);
        //var_dump($results);
        //exit;

        $this->setData($results[0]);
    }

    public function addProduct(Product $product){

        $sql = new Sql();

        $sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES (:idcart, :idproduct)", [
            ':idcart'=> $this->getidcart(),
            ':idproduct'=>$product->getidproduct()
        ]);   
        
        $this->getCalculateTotal();
    }

    public function removeProduct(Product $product, $all = false){

        $sql = new Sql();

        if($all){
            $sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW()  
                         WHERE idcart = :idcart 
                         AND idproduct = :idproduct  
                         AND dtremoved IS NULL", [
            ':idcart'=> $this->getidcart(),
            ':idproduct'=>$product->getidproduct()
            ]);        
        }else {
            $sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() 
                         WHERE idcart = :idcart 
                         AND idproduct = :idproduct 
                         AND dtremoved IS NULL LIMIT 1", [
            ':idcart'=> $this->getidcart(),
            ':idproduct'=>$product->getidproduct()
            ]);        
        }

        $this->getCalculateTotal();

    }


    public function getProducts(){
        $sql = new Sql();

        $rows =  
        $sql->select("SELECT b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, 
                             count(1) as nrqtd, sum(b.vlprice) as vltotal 
                    FROM tb_cartsproducts a 
                    INNER JOIN tb_products b ON 
                            a.idproduct = b.idproduct 
                    WHERE idcart = :idcart 
                    AND dtremoved IS NULL 
                    GROUP BY b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl
                    ORDER BY b.desproduct", [
                        ':idcart' => $this->getidcart()
                    ]);

        //var_dump($rows);
        //exit;

        return Product::checkList($rows);
    }


    public function getProductsTotals()
	{
		$sql = new Sql();
		$results = $sql->select("
			SELECT SUM(vlprice) AS vlprice, SUM(vlwidth) AS vlwidth, SUM(vlheight) AS vlheight, SUM(vllength) AS vllength, SUM(vlweight) AS vlweight, COUNT(*) AS nrqtd
			FROM tb_products a
			INNER JOIN tb_cartsproducts b ON a.idproduct = b.idproduct
			WHERE b.idcart = :idcart AND dtremoved IS NULL;
		", [
			':idcart'=>$this->getidcart()
		]);
		if (count($results) > 0) {
			return $results[0];
		} else {
			return [];
		}
    }
    
    public function setFreight($nrzipcode)
	{
		$nrzipcode = str_replace('-', '', $nrzipcode);
        $totals = $this->getProductsTotals();
        
		if ($totals['nrqtd'] > 0) {

            // se a altura for menor que 2, então recebe 2
            if ($totals['vlheight'] < 2) $totals['vlheight'] = 2;
            
            // se o comprimento for menor que 16, então recebe 16
            if ($totals['vllength'] < 16) $totals['vllength'] = 16;
            
            //qs => query string
			$qs = http_build_query([
				'nCdEmpresa'=>'',
				'sDsSenha'=>'',
				'nCdServico'=>'40010',
				'sCepOrigem'=>'69067000',
				'sCepDestino'=>$nrzipcode,
				'nVlPeso'=>$totals['vlweight'],
				'nCdFormato'=>'1',
				'nVlComprimento'=>$totals['vllength'],
				'nVlAltura'=>$totals['vlheight'],
				'nVlLargura'=>$totals['vlwidth'],
				'nVlDiametro'=>"0",
				'sCdMaoPropria'=>'S',
				'nVlValorDeclarado'=>$totals['vlprice'],
				'sCdAvisoRecebimento'=>'S'
            ]);
            
            /*************************************************/
            /*********** PARA DEBUGAR O RETORNO **************/
            /*************************************************/
            // [ CONVERTE PARA ARRAY ]
            // $xml = (array)simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs);
            // [ CONVERTE PARA JSON ] 
            // echo json_encode($xml);
            // exit;
            
            /**************************************************/
            /********** CÁLCULO DO FRETE SITE CORREIOS ********/
            /**************************************************/
			$xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs);
            $result = $xml->Servicos->cServico;
            
			if ($result->MsgErro != '') {
				Cart::setMsgError((string)$result->MsgErro);
			} else {
				Cart::clearMsgError();
			}
			$this->setnrdays($result->PrazoEntrega);
            $this->setvlfreight(Cart::formatValueToDecimal($result->Valor));
            /**/
            
            /**************************************************/
            /********** CÁLCULO DO FRETE MANUAL ********/
            /**************************************************/
            /*
            $result = '';
            $this->setnrdays(0);
            $this->setvlfreight(0);
            */

			$this->setdeszipcode($nrzipcode);
			$this->save();
			return $result;
        } 
        else {
        
        }
    }
    

    public static function formatValueToDecimal($value):float
	{
		$value = str_replace('.', '', $value);
		return str_replace(',', '.', $value);
    }
    

	public static function setMsgError($msg)
	{
		$_SESSION[Cart::SESSION_ERROR] = $msg;
    }
    

	public static function getMsgError()
	{
		$msg = (isset($_SESSION[Cart::SESSION_ERROR])) ? $_SESSION[Cart::SESSION_ERROR] : "";
		Cart::clearMsgError();
		return $msg;
    }
    

	public static function clearMsgError()
	{
		$_SESSION[Cart::SESSION_ERROR] = NULL;
    }
    
    public function updateFreight()
	{
		if ($this->getdeszipcode() != '') {
			$this->setFreight($this->getdeszipcode());
		}
    }

    //sobrescrevendo o método da classe pai
    public function getValues()
	{
		$this->getCalculateTotal();
		return parent::getValues();
	}
    
    public function getCalculateTotal()
	{
		$this->updateFreight();
		$totals = $this->getProductsTotals();
		$this->setvlsubtotal($totals['vlprice']);
		$this->setvltotal($totals['vlprice'] + (float)$this->getvlfreight()); //vlr + frete
    }
    
    public static function removeFromSession(){
        //var_dump($_SESSION);
        //exit;
        $_SESSION[Cart::SESSION] = NULL;
    }
    

}