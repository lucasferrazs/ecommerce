<?php
namespace Hcode\Model;


use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Model\Products;
class Category extends Model{

    public static function listAll()
    {
        $sql = new Sql();
        return $sql->select("SELECT * from tb_categories order by descategory");
    }

    public function save()
    {
        $sql = new Sql();

        $results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
            ":idcategory"=>$this->getidcategory(),
            ":descategory"=>$this->getdescategory()
           

        ));
        $this->setData($results[0]);

        Category::updateFile();
    }

   
    public function get($idcategory){

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory",[
            ":idcategory"=>$idcategory
        ]);

        $this->setData($results[0]);
    }

    public function delete()
    {
        $sql = new Sql();
        $sql->query("DELETE FROM tb_categories where idcategory = :idcategory", [
            //o this neste caso serve para trazer o atributo direto do metodo e nao como um parametro
            ':idcategory'=>$this->getidcategory()
        ]);
        Category::updateFile();
    }
    
    public static function updateFile(){

		$categories = Category::listAll();

		$html = array();

		foreach ($categories as $row ) {
			array_push($html, '<li><a href="/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>');
		}

		file_put_contents($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "categories-menu.html", implode('', $html));
	}

    public function getProducts($related = true)
    {
        $sql = new Sql();
        if ($related === true) {

            return $sql->select(
                "SELECT * FROM tb_products a 
                          WHERE EXISTS( SELECT 1 
                                         FROM tb_productscategories b
                                         WHERE b.idproduct = a.idproduct
                                         AND   b.idcategory = :idcategory);
                         ", [
                             ':idcategory'=>$this->getidcategory()
                         ]);
 
         } else {
             return $sql->select(
                 "SELECT * FROM tb_products a 
                          WHERE NOT EXISTS( SELECT 1 
                                             FROM tb_productscategories b
                                             WHERE b.idproduct = a.idproduct
                                             AND   b.idcategory = :idcategory);
             ", [
                 ':idcategory'=>$this->getidcategory()
             ]);
         }
     }
    
    public function getProductsPage($page = 1 , $itemsPerpage = 8)
    {
        $start = ($page - 1) * $itemsPerpage;

        $sql = new Sql();

        $results = $sql->select("SELECT SQL_CALC_FOUND_ROWS *  
                    FROM tb_products a 
                    WHERE EXISTS ( SELECT 1 
                        FROM tb_productscategories b
                        WHERE b.idproduct = a.idproduct
                        AND   b.idcategory = :idcategory)
                        LIMIT $start, $itemsPerpage;", [
                             ':idcategory'=>$this->getidcategory()
                        ]);

                        $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

                       return [
                        'data'=>Products::checkList($results),
                        'total'=>(int)$resultTotal[0]["nrtotal"],
                        'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerpage)
                       ];
    } 

    public function addProducts(Products $product)
    {
        $sql = new Sql();

        $sql->query("INSERT into tb_productscategories (idcategory,idproduct) values(:idcategory,:idproduct)", [
                ':idcategory'=>$this->getidcategory(),
                ':idproduct'=>$product->getidproduct()

        ]);
    }
    public function removeProducts(Products $product)
    {
        $sql = new Sql();

        $sql->query("DELETE from tb_productscategories where idcategory = :idcategory and idproduct = :idproduct", [
                ':idcategory'=>$this->getidcategory(),
                ':idproduct'=>$product->getidproduct()

        ]);
    }	
	public static function getPage($page = 1, $itemsPerPage = 10)
	{

		$start = ($page - 1) * $itemsPerPage;

		$sql = new Sql();

		$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_categories 
			ORDER BY descategory
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
			FROM tb_categories 
			WHERE descategory LIKE :search
			ORDER BY descategory
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
