<?php 

namespace Hcode\Model; // local onde vc está
use \Hcode\DB\Sql; // local do objeto que vc quer usar 
use \Hcode\Model;  // local do objeto que vc quer usar 
use \Hcode\Mailer; // local do objeto que vc quer usar 

class User extends Model {

    const SESSION = "User";
    const SECRET = "HcodePhp7_Secret"; //deve ter pelo menos 16 caracteres

    protected $fields = [
		"iduser", "idperson", "desperson", "deslogin", "despassword", "desemail", "nrphone", "inadmin", "dtergister"
	];

    public static function login($login, $password){
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
            ":LOGIN"=>$login
        ));

        if(count($results) === 0){
            throw new \Exception("Usuário inexistente ou senha inválida.");
        }

        $data = $results[0];

        if ( password_verify($password, $data["despassword"]) === true ){
            $user = new User();
            //$user->setiduser($data["iduser"]);
            $user->setData($data);

            $_SESSION[User::SESSION] = $user->getValues();

            return $user;
        }else{
            throw new \Exception("Usuário inexistente ou senha inválida.");
        }
    }

    public static function verifyLogin($inadmin = true){

        // se não foi definida
        // ou se foi definida mas está vazia ou perdeu o valor
        // se o casting do id do usuário não for maior que zero
        if(!isset($_SESSION[User::SESSION]) || !$_SESSION[User::SESSION] || 
                    !(int)$_SESSION[User::SESSION]["iduser"]>0 ||
                    (bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin){
            header("Location: /admin/login");
            exit;
        }
    }

    public static function logout()
    {
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
			":desperson"=>utf8_decode($this->getdesperson()),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>User::getPasswordHash($this->getdespassword()),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));
        $this->setData($results[0]);
        
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
        $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
            ":iduser"=>$iduser
        ));
        $this->setData($results[0]);
    }

    public function update()
	{
		$sql = new Sql();
		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":iduser"=>$this->getiduser(),
			":desperson"=>utf8_decode($this->getdesperson()),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>User::getPasswordHash($this->getdespassword()),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));
        $this->setData($results[0]);
        
        //var_dump($results);
        //exit;
    }

    public function delete(){
        $sql = new Sql();

        $results = $sql->select("CALL sp_users_delete(:iduser)", array(
            ":iduser"=>$this->getiduser()
        ));
    }

    public function getForgot($email){
        $sql = new Sql();

        $results = $sql->select("SELECT * from tb_persons 
                                 INNER JOIN tb_users b USING(idperson)  
                                 WHERE desemail = :email", array(
            ":email"=>$email
        ));

        if(count($results) === 0){
           throw new \Exception("Não foi possível recuperar a senha"); 
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
                $link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";

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
    
}

?>