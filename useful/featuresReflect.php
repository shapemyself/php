<?php

class Mysql{
    public function connect($args) {
        echo 'conencting to mysql host ' . $args[0] . ', port ' . $args[1],PHP_EOL;
    }

}

class Oracle{
    public function getDrive() {
        echo "dbname=oracle:host=localhost:port=6379",PHP_EOL;
    }

}


class DbProxy{
    private $proxys;

    public function __construct() {
    
        $this->proxys = []; 
    }

    public function addProxy($proxyObj) {
        $this->proxys[] = $proxyObj; 
    }

    public function __call($name, $args) {
        foreach ($this->proxys as $obj ) {
            $reflectionClass = new ReflectionClass($obj);

            try{
                
                if ( $method = $reflectionClass->getMethod($name) ) {
                    //$method is class of ReflectionMthod
                    if ( $method->isPublic() && !$method->isAbstract() ) {
                        echo "方法前记录log\n";
                        $method->invoke($obj,$args);
                        echo "方法后记录log\n";
                    }
            } 

            }catch(Exception $e) {

                continue;
            }
    }
    
}


}

$dbProxy = new DbProxy();
$dbProxy->addProxy(new Mysql());
$dbProxy->addProxy(new Oracle());
$dbProxy->connect('127.0.0.1', '6379');
$dbProxy->getDrive();







