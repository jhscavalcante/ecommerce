<?php 

namespace Hcode\Model; // local onde vc está
use \Hcode\DB\Sql; // local do objeto que vc quer usar 
use \Hcode\Model;  // local do objeto que vc quer usar 

class Product extends Model {

    public static function listAll()
    {
        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");
    }

    public static function checkList($list){
        foreach ($list as &$row) {
            $p = new Product();
            $p->setData($row);
            $row = $p->getValues();
        }
        return $list;
    }

    public function save()
	{
		$sql = new Sql();
        $results = $sql->select(
        "CALL sp_products_save(:idproduct,:desproduct,:vlprice,:vlwidth,:vlheight,:vllength,:vlweight,:desurl)",[ 
			":idproduct"=>$this->getidproduct(),
			":desproduct"=>$this->getdesproduct(),
			":vlprice"=>$this->getvlprice(),
			":vlwidth"=>$this->getvlwidth(),
			":vlheight"=>$this->getvlheight(),
			":vllength"=>$this->getvllength(),
			":vlweight"=>$this->getvlweight(),
			":desurl"=>$this->getdesurl()
            ]);
        $this->setData($results[0]);                
    }
    
    public function get($idproduct){
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", array(
            ":idproduct"=>$idproduct
        ));
        $this->setData($results[0]);
    }

    public function delete(){

        $localArquivo = $_SERVER['DOCUMENT_ROOT'] . 
        DIRECTORY_SEPARATOR . "res" . 
        DIRECTORY_SEPARATOR . "site" .
        DIRECTORY_SEPARATOR . "img" .
        DIRECTORY_SEPARATOR . "products" .
        DIRECTORY_SEPARATOR . $this->getidproduct() . ".jpg";

        if(file_exists($localArquivo)){
            unlink($localArquivo);
        }

        $sql = new Sql();

        $sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", [
            ':idproduct'=>$this->getidproduct()
        ]);
    }

    public function checkPhoto()
    {
        // caminho de pastas do sistema operacional
        if(file_exists(
            $_SERVER['DOCUMENT_ROOT'] . 
            DIRECTORY_SEPARATOR . "res" . 
            DIRECTORY_SEPARATOR . "site" .
            DIRECTORY_SEPARATOR . "img" .
            DIRECTORY_SEPARATOR . "products" .
            DIRECTORY_SEPARATOR . $this->getidproduct() . ".jpg"
            ))
        {
            // url
            $url = "/res/site/img/products/" . $this->getidproduct() . ".jpg"; 
        }else{
            $url = "/res/site/img/products/product.jpg"; 
        }

        return $this->setdesphoto($url);
    }

    public function getValues()
    {
        $this->checkPhoto();
        $values = parent::getValues();

        return $values;
    }

    public function setPhoto($file){

        //pega o nome do arquivo e onde tem um ponto cria um array e seta os valores no array
        $extension = explode('.', $file['name']);
        $extension = strtolower(end($extension)); // pega a última posição do array e converte para minúsculo

       

        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($file['tmp_name']);
                break;            
            case 'gif':
                $image = imagecretefromgif($file['tmp_name']);
                break;
            case 'png':
                $image = imagecreatefrompng($file['tmp_name']);
                break;
            //default:
            //     $image = imagecreatefromjpeg($file['tmp_name']);
            //    break;
        }

        $dist = $_SERVER['DOCUMENT_ROOT'] . 
                DIRECTORY_SEPARATOR . "res" . 
                DIRECTORY_SEPARATOR . "site" .
                DIRECTORY_SEPARATOR . "img" .
                DIRECTORY_SEPARATOR . "products" .
                DIRECTORY_SEPARATOR . $this->getidproduct() . ".jpg";

        //salva a imagem crida no destino
        imagejpeg($image, $dist);

        imagedestroy($image);

        $this->checkPhoto();
    }    

    public function getFromURL($desurl){
        $sql = new Sql();
        $rows = $sql->select("SELECT * FROM tb_products WHERE desurl = :desurl LIMIT 1", [
            "desurl"=> $desurl
        ]);

        $this->setData($rows[0]);
    }

    public function getCategories(){

        $sql = new Sql();

        return $sql->select("SELECT * FROM tb_categories a  
                             INNER JOIN tb_productscategories b ON 
                                        a.idcategory = b.idcategory 
                             WHERE b.idproduct = :idproduct", [
			        ':idproduct'=>$this->getidproduct()
		]);
    }
}

?>