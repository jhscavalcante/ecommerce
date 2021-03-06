<?php 

namespace Hcode\Model; // local onde vc está
use \Hcode\DB\Sql; // local do objeto que vc quer usar 
use \Hcode\Model;  // local do objeto que vc quer usar 
use \Hcode\Mailer; // local do objeto que vc quer usar 

class User extends Model {

    const SESSION = "User";
    const SECRET = "HcodePhp7_Secret"; //deve ter pelo menos 16 caracteres
    const ERROR = "UserError";
	const ERROR_REGISTER = "UserErrorRegister";
	const ERROR_LINK_FORGOT_EXPIRED = "UserErrorLinkForgotExpired";
	const SUCCESS = "UserSucesss";

    //protected $fields = [
	//	"iduser", "idperson", "desperson", "deslogin", "despassword", "desemail", "nrphone", "inadmin", "dtergister"
    //];
    
    public static function getFromSession(){

        $user = new User();


        if(isset($_SESSION[User::SESSION]) && ((int)$_SESSION[User::SESSION]['iduser'] > 0) ){            
            $user->setData($_SESSION[User::SESSION]);
        }

        return $user;
    }

    public static function checkLogin($inadmin = true){
        // se não foi definida
        // ou se foi definida mas está vazia ou perdeu o valor
        // se o casting do id do usuário não for maior que zero
        if(!isset($_SESSION[User::SESSION]) || !$_SESSION[User::SESSION] || 
                    !(int)$_SESSION[User::SESSION]["iduser"]>0 )
        {
            // Não está logado
            return false;
        }else{

            if($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true){
                return true;
            }else if ($inadmin === false){
                return true;
            }else {
                return false;
            }
        }
    }

    public static function login($login, $password){
        $sql = new Sql();

        $results = 
        $sql->select("SELECT * 
                      FROM tb_users a 
                           INNER JOIN tb_persons b ON 
                                      a.idperson = b.idperson 
                      WHERE a.deslogin = :LOGIN", array(
			":LOGIN"=>$login
		)); 

        if(count($results) === 0){
            throw new \Exception("Usuários não existe ou senha inválida.");
        }

        $data = $results[0];

        if ( password_verify($password, $data["despassword"]) === true ){
            $user = new User();
            //$user->setiduser($data["iduser"]);

            //$data['desperson'] = utf8_encode($data['desperson']);
            $user->setData($data);

            //var_dump($data);
            //exit;

            $_SESSION[User::SESSION] = $user->getValues();

            return $user;
        }else{
            throw new \Exception("Usuário inexistente ou senha inválida.");
        }
    }

    public static function verifyLogin($inadmin = true){

        if(!User::checkLogin($inadmin)){
            if($inadmin){
                header("Location: /admin/login");
            }else{
                header("Location: /login");
            }            
            exit;
        }
    }

    public static function logout()
    {
        //var_dump($_SESSION);
        //exit;
        $_SESSION[User::SESSION] = NULL;
    }

    public static function listAll()
    {
        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");
    }

    public function save()
	{
		$sql = new Sql();
		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":desperson"=>$this->getdesperson(),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>User::getPasswordHash($this->getdespassword()),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));
        $this->setData($results[0]);
        
        // atualiza as informações da sessão após o INSERT ou UPDATE (PRINCIPALMENTE)
        $_SESSION[User::SESSION] = $this->getValues();
        //var_dump($results);
        //exit;
    }
    
    public static function getPasswordHash($password)
	{
        //parãmetros:
        // 1 => senha a ser criptografada
        // 2 => modo de criptografia
        // 3 => custo de processamento do servidor para gerar (quanto maior é melhor, entretanto, mais demorado fica) 
		return password_hash($password, PASSWORD_DEFAULT, [
            //return password_hash($password, PASSWORD_BCRYPT, [
			'cost'=>12
		]);
    }
    
    public function get($iduser){
        $sql = new Sql();

        $results = 
        $sql->select("SELECT * 
                      FROM tb_users a 
                      INNER JOIN tb_persons b 
                                 USING(idperson) 
                      WHERE a.iduser = :iduser", array(
                      ":iduser"=>$iduser
                      ));

        $data = $results[0];
        //$data['desperson'] = utf8_encode($data['desperson']);

        $this->setData($data);
    }

    public function update()
	{
		$sql = new Sql();
		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":iduser"=>$this->getiduser(),
			":desperson"=>$this->getdesperson(),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>$this->getdespassword(),
			//":despassword"=>User::getPasswordHash($this->getdespassword()),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));
        $this->setData($results[0]);

        //var_dump($results[0]['desperson']);
        //exit();

         // atualiza as informações da sessão após o INSERT ou UPDATE (PRINCIPALMENTE)
        $_SESSION[User::SESSION] = $this->getValues();
        
        //var_dump($results);
        //exit;
    }

    public function delete(){
        $sql = new Sql();

        $results = $sql->select("CALL sp_users_delete(:iduser)", array(
            ":iduser"=>$this->getiduser()
        ));
    }

    public function getForgot($email, $inadmin = true){
        $sql = new Sql();

        $results = $sql->select("SELECT * from tb_persons 
                                 INNER JOIN tb_users b USING(idperson)  
                                 WHERE desemail = :email", array(
            ":email"=>$email
        ));

        if(count($results) === 0){
           throw new \Exception("E-mail inválido!"); 
        }else{
           $data = $results[0];
           $results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
                ":iduser"=>$data["iduser"],
                ":desip"=>$_SERVER["REMOTE_ADDR"]
           )); 

           if(count($results2) === 0){
                throw new \Exception("Não foi possível recuperar a senha"); 
           }else{
                $dataRecovery = $results2[0];
                //base64 transforma códigos que não não legíveis em texto, deste modo o 
                // conteúdo ao trafegar pela internet não será perdido

                //parãmetros:
                //1 = Tipo de Criptografia, por exemplo 128 bits, 256 bits ...
                //2 = Nome da Chave de Segurança
                //3 = Dados que serão criptografados, neste caso o valor do campo [idrecovery] da tabela tb_userspasswordsrecoveries
                //4 = Modo de Criptografia, neste caso randômico
                $code = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, User::SECRET, 
                                            $dataRecovery["idrecovery"], MCRYPT_MODE_ECB
                            ));

                if($inadmin === true){
                    $link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";
                }else{
                    $link = "http://www.hcodecommerce.com.br/forgot/reset?code=$code";
                }                

                $mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir Senha da Hcode Store",
                          "forgot", array(
                              "name"=>$data["desperson"],
                              "link"=>$link
                          ));
                
                $mailer->send();
                
                return $data;
           }
        }
    }

    public static function validForgotDecrypt($code){
        
        $idrecovery = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, User::SECRET, base64_decode($code), MCRYPT_MODE_ECB);

        $sql = new Sql();
        $results= $sql->select("SELECT * 
                                FROM tb_userspasswordsrecoveries a 
                                    INNER JOIN tb_users b USING(iduser)
                                    INNER JOIN tb_persons c USING(idperson)
                                WHERE a.idrecovery = :idrecovery
                                AND   a.dtrecovery IS NULL     
                                AND   DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW()", array(
                                    ":idrecovery"=>$idrecovery
                                ));

        if(count($results) === 0){
            throw new \Exception("Limite de tempo para troca de senha expirado!"); 
        }else{
            return $results[0];
        }
    }

    public static function setForgotUsed($idrecovery){
        $sql = new Sql();
        $sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() 
                       WHERE idrecovery = :idrecovery and dtrecovery IS NULL", array(
                           ":idrecovery"=>$idrecovery
                       ));
    }

    public function setPassword($password){
        $sql = new Sql();
        $sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
            ":password"=>User::getPasswordHash($password),
            ":iduser"=>$this->getiduser()
        ));
    }


    public static function checkLoginExist($login)
	{
		$sql = new Sql();
		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :deslogin", [
			':deslogin'=>$login
		]);
		return (count($results) > 0);
    }
    
    
    /***************************************************************************/
    /************************ MGS DE ERROR AND SUCCESS *************************/
    /***************************************************************************/
    public static function setError($msg)
	{
		$_SESSION[User::ERROR] = $msg;
    }
    
	public static function getError()
	{
        //verifica se o erro está definido e se foi definido se ele não está vazio, ou seja se tem valor
		$msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : '';
		User::clearError();
		return $msg;
    }
    
	public static function clearError()
	{
		$_SESSION[User::ERROR] = NULL;
    }
    
	public static function setSuccess($msg)
	{
		$_SESSION[User::SUCCESS] = $msg;
    }
    
	public static function getSuccess()
	{
		$msg = (isset($_SESSION[User::SUCCESS]) && $_SESSION[User::SUCCESS]) ? $_SESSION[User::SUCCESS] : '';
		User::clearSuccess();
		return $msg;
    }
    
	public static function clearSuccess()
	{
		$_SESSION[User::SUCCESS] = NULL;
    }


    /***************************************************************************/
    /************************ MGS DE ERROR FORGOT LINK *************************/
    /***************************************************************************/
    public static function setErrorLinkForgotExpired($msg)
	{
		$_SESSION[User::ERROR_LINK_FORGOT_EXPIRED] = $msg;
    }
    
	public static function getErrorLinkForgotExpired()
	{
        //verifica se o erro está definido e se foi definido se ele não está vazio, ou seja se tem valor
        $msg = (isset($_SESSION[User::ERROR_LINK_FORGOT_EXPIRED]) && 
                $_SESSION[User::ERROR_LINK_FORGOT_EXPIRED]) ? $_SESSION[User::ERROR_LINK_FORGOT_EXPIRED] : '';
		User::clearErrorLinkForgotExpired();
		return $msg;
    }
    
	public static function clearErrorLinkForgotExpired()
	{
		$_SESSION[User::ERROR_LINK_FORGOT_EXPIRED] = NULL;
    }
    

    /***************************************************************************/
    /******************** VALIDAÇÕES DO REGISTRO DE USUÁRIO ********************/
    /***************************************************************************/
    public static function setErrorRegister($msg)
	{
		$_SESSION[User::ERROR_REGISTER] = $msg;
    }
    
	public static function getErrorRegister()
	{
		$msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : '';
		User::clearErrorRegister();
		return $msg;
    }
    
	public static function clearErrorRegister()
	{
		$_SESSION[User::ERROR_REGISTER] = NULL;
    }
    

    /***************************************************************************/
    /******************************** ORDERS ***********************************/
    /***************************************************************************/
    public function getOrders()
	{
		$sql = new Sql();
		$results = $sql->select("   SELECT * 
                                    FROM tb_orders a 
                                    INNER JOIN tb_ordersstatus b USING(idstatus) 
                                    INNER JOIN tb_carts c USING(idcart)
                                    INNER JOIN tb_users d ON d.iduser = a.iduser
                                    INNER JOIN tb_addresses e USING(idaddress)
                                    INNER JOIN tb_persons f ON f.idperson = d.idperson
                                    WHERE a.iduser = :iduser", [
			                        ':iduser'=>$this->getiduser()
		                        ]);
		return $results;
    }
    


    /***************************************************************************/
    /******************************* PAGINAÇÃO *********************************/
    /***************************************************************************/
    public static function getPage($page = 1, $itemsPerPage = 10)
	{
        $start = ($page - 1) * $itemsPerPage;
        
		$sql = new Sql();
		$results = $sql->select(" SELECT SQL_CALC_FOUND_ROWS *
                                    FROM tb_users a 
                                    INNER JOIN tb_persons b USING(idperson) 
                                    ORDER BY b.desperson
                                    LIMIT $start, $itemsPerPage;
                                ");
        
        $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");
        
		return [
			'data'=>$results,
			'total'=>(int)$resultTotal[0]["nrtotal"],
			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		];
	}


    public static function getPageSearch($search, $page = 1, $itemsPerPage = 10)
	{
		$start = ($page - 1) * $itemsPerPage;
		$sql = new Sql();
		$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_users a 
			INNER JOIN tb_persons b USING(idperson)
			WHERE b.desperson LIKE :search OR b.desemail = :search OR a.deslogin LIKE :search
			ORDER BY b.desperson
			LIMIT $start, $itemsPerPage;
		", [
			':search'=>'%'.$search.'%'
		]);
		$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");
		return [
			'data'=>$results,
			'total'=>(int)$resultTotal[0]["nrtotal"],
			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		];
	} 

    
}

?>