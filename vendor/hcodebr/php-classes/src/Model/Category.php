<?php 

namespace Hcode\Model; // local onde vc está
use \Hcode\DB\Sql; // local do objeto que vc quer usar 
use \Hcode\Model;  // local do objeto que vc quer usar 
use \Hcode\Mailer; // local do objeto que vc quer usar 

class Category extends Model {

    public static function listAll()
    {
        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");
    }

    public function save()
	{
		$sql = new Sql();
		$results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
			":idcategory"=>$this->getidcategory(),
			":descategory"=>$this->getdescategory()
		));
        $this->setData($results[0]);
    }
    
    
    
    public function get($idcategory){
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", array(
            ":idcategory"=>$idcategory
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

        $sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", [
            ':idcategory'=>$this->getidcategory()
        ]);
    }
    
}

?>