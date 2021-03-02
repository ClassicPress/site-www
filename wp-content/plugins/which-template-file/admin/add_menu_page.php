<?php
/**
 * Fichier d'exemple à recopier pour créer une page d'administration.
 * -----------------------------------------------------------
 * 
 * Page d'administration
 * Contiendra un tableau, ou pas.
 * Pour obtenir l'url de cette page d'admin :
 * $url = parent::url();
 * 
 * Pour y ajouter des paramètres, les ajouter en param sous forme de array
 * $url = parent::url(array('foo' => 'bar'));
 * 
 * @author Gilles Dumas <circusmind@gmail.com>
 * @date    20150225
 * @version 20151028
 * @link http://codex.wordpress.org/Function_Reference/add_users_page
 * @link http://codex.wordpress.org/Function_Reference/add_menu_page
 */

class wtf_admin_page extends class_page_admin {
    
    /**
     * Start up
     */
    public function __construct() {
        // die('wtf_admin_page __construct');
        
        add_action('admin_menu', array( $this, 'add_plugin_page'));
        add_action('admin_init', array( $this, 'page_init'));	
        add_action('admin_head', array( $this, 'add_action_admin_head'));    
        parent::__construct(get_class());
        
        $this->text = new stdClass;
        
        // Il faut paramétrer tout ceci
        $this->text->tag_title  = 'Which Template File Options';
        $this->text->menu_title = 'Which Template File';
        $this->text->page_h2    = 'Which Template File Options';
    }

	/**
	* Ajout de la page
	* @author Gilles Dumas <circusmind@gmail.com>
	* @since 20150918
	* @link http://codex.wordpress.org/Function_Reference/add_menu_page
	*/
	function add_plugin_page() {
        
        $page_title = $this->text->tag_title;
        $menu_title = $this->text->menu_title;
        $capability = 'manage_options';
        $menu_slug  = get_class();
        $function   = array($this, 'display_admin_page');
        $icon_url   = null;
        $position   = '9996';
        
        add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position);
    }

	/**
	* Options page callback
	* @author Gilles Dumas <circusmind@gmail.com>
	* @since 20150918
	*/
	function display_admin_page() {
		$this->display_my_admin_page();
	}
	
	/**
	* 
	* @author Gilles Dumas <circusmind@gmail.com>
	* @since 20140729
	*/
	function display_my_admin_page() {
        
        $title = $this->text->page_h2;
        parent::display_box_begin($title, 'display_admin_page');
        
        $wtf_option_1 = get_option(_WTF_OPTION_1);
        ?>
        
        <form name="newsletters-filter" id="newsletters-filter" method="get" action="?">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
            
            <p>Who can see the template file name in the admin bar ?</p>
            
            <?php
            $checked = '';
            if ($wtf_option_1 == 'administrator' || $wtf_option_1 == false) {
                $checked = 'checked';
            }
            ?>
            <label>Administrators only
                <input type="radio" name="<?php echo _WTF_OPTION_1; ?>" value="administrator" <?php echo $checked; ?>>
            </label><br />
            
            <?php
            $checked = '';
            if ($wtf_option_1 == 'all') {
                $checked = 'checked';
            }
            ?>
            <label>Every logged user
                <input type="radio" name="<?php echo _WTF_OPTION_1; ?>" value="all" <?php echo $checked; ?>>
            </label><br /><br />
            
            <input type="submit" class="button button-primary" />
            
        </form>
        <?php
        parent::display_box_stop();
    }
    
	
    /**
     * Les actions à effectuer au cas où l'utilisateur vienne de cliquer sur un lien
     * avec des paramètres.
     */
    public function page_init() {
        if (isset($_GET[_WTF_OPTION_1])) {
            if ($_GET[_WTF_OPTION_1] == 'administrator' || $_GET[_WTF_OPTION_1] == 'all') {
                update_option(_WTF_OPTION_1, $_GET[_WTF_OPTION_1]);
                $this->notice_msg   = 'Setting updated !';
                $this->notice_class = 'updated';
            }
            else {
                $this->notice_msg   = 'Bad setting value !';
                $this->notice_class = 'error';
            }
            add_action( 'admin_notices', array( $this, 'my_admin_notice' ));
        }
    }
	
    
    /**
     * 
     */
    public function my_admin_notice() {
        ?>
        <div class="<?php echo $this->notice_class; ?>">
            <p><?php echo $this->notice_msg; ?></p>
        </div>
        <?php
        // On ré-initialise ces deux variables
        $this->notice_class = $this->notice_msg = '';
    }
    

    /**
     * Ajout de code dans le <head>
     */
    public function add_action_admin_head() {
        parent::admin_head(get_class());
    }
    
    
    
	/**
	* Génération des liens de bas de page
	*/
	function set_links_footer() {
		
		$this->links_footer = [];
		
        // // Lien vers page de tous les paiements
        // $this->links_footer[paiement_page::url()] = 'Les paiements';

        // // Lien vers page de tous les newsletters
        // $this->links_footer[newsletter_batch_page::url()] = 'Les newsletters';

	}
    
}


if( is_admin() )
    $wtf_admin_page = new wtf_admin_page;

    
