<?php
/**
 * @author Gilles Dumas <circusmind@gmail.com>
 * @since   20140713
 * @version 20151028
 */
 
abstract class class_page_admin {

    /**
     * Le nom de la classe fille qui a étendu celle-là
     */
    private $get_called_class;

    /**
     * Les liens de bas de page
     */
    protected $links_footer;
    
    /**
     * Start up !
     */
    public function __construct() {
        $this->get_called_class = 'wtf_admin_page';
	}

    /**
     * Cette fonction renvoie l'url de cette page d'admin.
     * Elle peut être appelée de n'importe où, notament de l'extérieur de cette classe.
     * Appelée de cette manière : newsletter_batch_page::url();
     * On peut aussi l'appeler en ajoutant des paramètres en faisant comme ça :
     * newsletter_batch_page::url(array('foo'=>'bar', 'foo2'=>'bar2'));
	 * @link http://www.php.net/manual/en/function.get-called-class.php
     */
    public static function url($params=null) {
        $url = add_query_arg(array(
                'page' => 'wtf_admin_page'
            ),
            admin_url('admin.php')
        );
        if (!is_null($params)) {
            $url = add_query_arg($params, $url);
        }
        return $url;
    }
    
    /**
     * The end !
     */
    public function __destruct() {
	}
	
	
	/**
	* Le code html au début de toute box.
	* @author Gilles Dumas <circusmind@gmail.com>
	* @since 20140713
    * @param $id Integer Identifiant css de la box
    * @param $str String Le titre de la box
	*/
	function display_box_begin($box_title, $id=null) {
		?>
        <div class="wrap">
            
            <div id="icon-users" class="icon32"><br /></div>
            <h2><?php echo $box_title; ?></h2>
        <?php return; ?>
            
        <?php /* OBSOLETE ? */ ?>
		<div class="wrap my_backoffice" id="<?php echo $id; ?>">
            <h1 class="hndle" style=""><?php echo $box_title; ?></h1>
				<div class="inside">
		<?php
	}
	
    
	/**
	* Le code html à la fin de toute box.
	* @author Gilles Dumas <circusmind@gmail.com>
	* @since 20140923
	*/
	function display_box_stop($nb_total_items=0) {
		?></div><!--/.wrap--><?php
        $this->display_links_footer();
	}
	

	/**
	* Affiche les liens de bas de page
	*/
	function display_links_footer() {
        $this->set_links_footer();
        
		$count_links = count($this->links_footer);
		if ($count_links) {
            echo '<div id="links_footer_container" style="padding:10px 3px;margin:12px 3px;">';
                $count = 0;
                foreach ($this->links_footer as $href => $lbl) {
                    echo '<a href="'.$href.'">'.$lbl.'</a>';
                    $count++;
                    if ($count != $count_links) {
                        echo ' | ';
                    }
                }
            echo '</div>';
		}
	}
	
    /**
     * Ajout de code dans le <head>
     */
    function admin_head() {
        ?>
        <style type="text/css">
            body.toplevel_page_<?php echo $this->get_called_class; ?> div#wpbody-content {
                /* Pour corriger le 65px que wp met par défaut */
                padding-bottom:0px;
                /* parce que ! */
                float:none;
            }
        </style>
        <?php
    }
	

    
}




















