<?php

require ('db_functions.php');
require ('cart_fns.php');

//connexion à la BD
$connection = db_connect();

//gestion des sessions pour la corbeille et les achats
session_start();
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
    $_SESSION['total_items'] = 0;
    $_SESSION['total_price'] = 0;
}

// config des parametres de la PAGINATION
if (isset($_GET['page']) && $_GET['page'] > 0) {
    $currentPage = $_GET['page'];
} else {
    $currentPage = 1;
}
if(isset($_GET['id'])){
    $nbrArt = get_nb_art_by_cat($connection,$_GET['id']);
}else{
    $nbrArt = get_nb_art_all($connection);
}
$perPage = 6;
$nbrPages = ceil($nbrArt / $perPage);

//selection de view pour acceder à la page souhaitée
$view = empty($_GET['view']) ? 'index' : $_GET['view'];
switch ($view) {
    case('index'):
        $products = get_products_as_array($connection, $currentPage, $perPage);
        break;
    case('cat'):
        //on recupere les produits selon la categorie
        $products = get_products_by_cat($_GET['id'], $connection,$currentPage,$perPage);
        break;
    case('product'):
        //on recupere le produit selon son id
        $product = get_product($_GET['id'], $connection);
        //recuperer la description et la separer
        $rows_desc = split(';', $product['desc']);
        //supprimer le dernier element qui apparait et qui est vide et qui crée un * vide
        //ma faute, qd j'ai rempli la bd --> pas envie de tt changer à la main
        $i = count($rows_desc) - 1;
        unset($rows_desc[$i]);
        break;
    case ('cart'):
        // o_O
        break;
    case ('clearCart'):
        unset($_SESSION['cart']);
        header('Location: index.php?view=cart');
        break;
    case('add_to_cart'):
        //on n'appele pas une autre page!!!!!
        //on recupere l'id du l'elem, suite à l'appuie du brn "Ajouter au panier"
        //et grace à cet id on ajoute l'element dans le panier --> fct: add_to_cart()
        $id_prod = $_GET['id'];
        add_to_cart($id_prod);
        $_SESSION['total_items'] = total_items($_SESSION['cart']);
        $_SESSION['total_price'] = total_price($_SESSION['cart'], $connection);
        //readresser la page là ou on a été. -->à retravailler pour les pages cat/index/prod + les num pages 
        if($_GET['location']=='index'){
            header('Location: index.php?page='.$_GET['page']);
        }elseif ($_GET['location'] == 'cat') {
            header('Location: index.php?view=cat&id='.$_GET['cat'].'&page='.$_GET['page']);
        }else{
            header('Location: index.php?view=product&id=' . $id_prod);
        }
        break;
    case('update_cart'):
        update_cart();
        $_SESSION['total_items'] = total_items($_SESSION['cart']);
        $_SESSION['total_price'] = total_price($_SESSION['cart'], $connection);
        header('Location: index.php?view=cart');
        break;
    case ('order'):

        break;
    case ('login'):
        $user = $_POST['login'];
        var_dump($user);
        $password = sha1($_POST['password']);
        var_dump($password);
        die();
        if(check_user($connection, $user, $password)){
            // si l'utilisateur existe
            $_SESSION['user'] = get_username($connecion, $user);
            var_dump($_SESSION['user']);
            die();
        }else{
            
        }
        header('Location: index.php?view=index');
        break;
    case('logout'):
        unset($_SESSION['user']);
        header('Location: index.php?view=index');
        break;
}

//protection
$arr = array('index', 'cat', 'product', 'cart', 'add_to_cart', 'update_cart', 'order');
if (!in_array($view, $arr))
    die("ERROR 404<br>Cette adrese n'existe pas...!!! o_O");

//ici on charge la carcasse 
include ($_SERVER['DOCUMENT_ROOT'] . '/okiStore/views/layouts/main.php');
?>