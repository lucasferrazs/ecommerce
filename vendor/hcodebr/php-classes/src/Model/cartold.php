<?php
namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Model\User;
use \Hcode\Model\Category;


class Cart extends Model{

    const SESSION = "Cart";
    
    public static function getFromSession()
    {
        $cart = new Cart();

        if (isset($_SESSION[Cart::SESSION]) && $_SESSION[Cart::SESSION]['idcart'])
        {   
            $cart->get((int)$_SESSION[Cart::SESSION]['idcart']);
        }
        else
            {
                $cart->getFromSessionID();


                if (!(int)$cart->getidcart() > 0)
                {
                    $data = [
                        'desssessionid'=>session_id() 
                    ];
                    if(User::checkLogin(false) === true){

                         $user = User::getFromSession();

                         $data["iduser"] = $user->getiduser();
                    
                        }

                    $cart->setData($data);

                    $cart->save();

                    $cart->setToSession();
                }
            }

            return $cart;
    }
    public function setToSession()
    {
        $_SESSION[Cart::SESSION] = $this->getValues();
    }

    public function get(int $idcart)
    {
        $sql = new Sql();

        $results = $sql->select("SELECT * from tb_carts where idcart = :idcart",[
                ":idcart"=>$idcart
       ]);
       if (count($results) > 0) {

			
        $this->setData($results[0]);
        
    }
    }

    public function getFromSessionID()
    {
        $sql = new Sql();

        $results = $sql->select("SELECT * from tb_carts where dessessionid = :dessessionid", [
                ":dessessionid"=>session_id()
        ]);
            if(count($results ) > 0 ){

                $this->setData($results[0]);

             }
    }


    public function save()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", [
			':idcart'=>$this->getidcart(),
			':dessessionid'=>$this->getdessessionid(),
			':iduser'=>$this->getdesiduser(),
			':deszipcode'=>$this->getdeszipcode(),
			':vlfreight'=>$this->getvlfreight(),
			':nrdays'=>$this->getnrdays()
		]);

		$this->setData($results[0]);

	}
}
?>