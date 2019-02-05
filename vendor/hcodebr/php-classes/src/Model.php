<?php 

namespace Hcode; // ela está no namespace principal

class Model{

    private $values = [];

    // $name => nome do método invocado / $args => os argumentos que foram passados
    public function __call($name, $args){

        $method = substr($name, 0, 3);
        $fieldName = substr($name, 3, strlen($name)); // a partir da 3 posição até o final

        switch($method)
        {
            case "get":
                return $this->values[$fieldName];
                break;
            case "set":
                $this->values[$fieldName] = $args[0];
                break;
        }
    }

    public function setData($data = array()){

        //key => campo / $value => valor do campo
        //irá inserir no array $values
        foreach ($data as $key => $value) {
            $this->{"set".$key}($value);
        }
    }

    public function getValues()
    {
        return $this->values;
    }
}


?>