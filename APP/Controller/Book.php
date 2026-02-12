<?php
/**
 * Created by PhpStorm.
 * User: yushi
 * Date: 2017/9/16
 * Time: 17:56
 */
class Controller_Book{
    function actionIndex(){
        echo 'Book controller default action';
    }
    function actionSayTitle(){
        $namespace=<<<sss
        1.全局函数的命名规则：</br>
          全局函数是用_分割全小写的单词，例如get_cache(),同时函数名采用动词+宾语形式，例如write_cache();</br>
         2.类的命名规则：</br>
         所有fleaphp自带的类，都是FLEA_开头，然后根据用途命名。例如 FLEA_Controller_Action、FLEA_Helper_imgCode.</br>
         然后将类中的名字_替换为目录分隔符，这就是这个类的定义文件所在位置。</br>
         FLEA_Controller_Action类   文件位置 FLEA/Controller/Action.php  </br>
         FLEA_Db_TableDataGateway类   文件位置 FLEA/Db/TableDataGateway.php</br>
         3.变量和常量命名
         全局变量和常量都使用全大写，以_分割。例如\$GLOBALS['CLASS_PATH']和FLEA_DIR;
         函数类方法中使用的变量都为临时变量，命名规则是第一个单词小写，后续的单词第一个字母大写。\$requestFilters,\$dispatcherClass等。
sss;


        echo $namespace;
    }
}
