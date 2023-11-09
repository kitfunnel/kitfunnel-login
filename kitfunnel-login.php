<?php
/*
Plugin Name: KitFunnel Login
Plugin URI: https://kitfunnel.com
Description: Personalización CN
Version: 1.1.9
Author: KitFunnel
License: GPL 2+
License URI: https://kitfunnel.com */ 

require_once "kitfunnellogin-base.php";
class KitFunnelLogin {
    public $plugin_file=__FILE__;
    public $response_obj;
    public $license_message;
    public $show_message=false;
    public $slug="kitfunnel-login";
    public $plugin_version='';
    public $text_domain='';
    function __construct() {
        add_action( 'admin_print_styles', [ $this, 'set_admin_style' ] );
        $this->set_plugin_data();
	    $main_lic_key="KitFunnelLogin_lic_Key";
	    $lic_key_name =Kit_Funnel_Login_Base::get_lic_key_param($main_lic_key);
        $license_key=get_option($lic_key_name,"");
        if(empty($license_key)){
	        $license_key=get_option($main_lic_key,"");
	        if(!empty($license_key)){
	            update_option($lic_key_name,$license_key) || add_option($lic_key_name,$license_key);
            }
        }
        $lice_email=get_option( "KitFunnelLogin_lic_email","");
        Kit_Funnel_Login_Base::add_on_delete(function(){
           update_option("KitFunnelLogin_lic_Key","");
        });
        if(Kit_Funnel_Login_Base::check_wp_plugin($license_key,$lice_email,$this->license_message,$this->response_obj,__FILE__)){
            add_action( 'admin_menu', [$this,'active_admin_menu'],99999);
            add_action( 'admin_post_KitFunnelLogin_el_deactivate_license', [ $this, 'action_deactivate_license' ] );
            //$this->licenselMessage=$this->mess;



add_action('login_enqueue_scripts','bs_custom_login_styles');function bs_custom_login_styles(){$css_version='1.7';$css_url=add_query_arg('v',$css_version,plugins_url('/css/styles-login.css',__FILE__));wp_enqueue_style('custom-login-styles',$css_url);}function my_login_logo(){$logo_url='/wp-content/plugins/kitfunnel-login/img/3d-login.png';echo'<style type="text/css"> .login h1 a { background-image: url('.$logo_url.') !important; } </style>';}add_action('login_enqueue_scripts','my_login_logo');add_filter('login_headerurl','bs_custom_login_logo_link');function bs_custom_login_logo_link($url){return '/cn/account/?display=inicio';}function custom_login_redirect($redirect,$user){if(isset($user->roles)&&is_array($user->roles)){if(in_array('administrator',$user->roles)){return admin_url();}elseif(in_array('subscriber',$user->roles)){return home_url('/cn/account/?display=inicio');}}return home_url('/cn/account/?display=inicio');}add_filter('login_redirect','custom_login_redirect',10,2);function css_kitfunnel_admin(){ ?><style>#adminmenu #menu-posts{display:block!important}</style><?php }add_action('admin_head','css_kitfunnel_admin');;function prefix_toolbar_styles(){if(is_admin()){echo '<style>#wp-toolbar .ab-icon:before { content: "\f180"; top: 2px; }</style>';}}add_action('admin_head','prefix_toolbar_styles');add_action('admin_bar_menu',function($barra_admin){if(is_admin()){$barra_admin->add_menu(array('id'=>'mi-menu','title'=>'<span class="ab-icon"></span> Mis Atajos','href'=>'','meta'=>array(),));$barra_admin->add_menu(array('id'=>'herramientas-ind-tw','title'=>'Herramientas','parent'=>'mi-menu','href'=>'','meta'=>array(),));$barra_admin->add_menu(array('id'=>'wp-pusher-main','title'=>'Gestión de Plugins','parent'=>'herramientas-ind-tw','href'=>'','meta'=>array(),));$slug_personalizado='wp-admin/network/admin.php?page=wppusher-plugins';$url_personalizada=home_url($slug_personalizado);$barra_admin->add_menu(array('id'=>'wp-pusher-update','title'=>'Actualizar plugins','parent'=>'wp-pusher-main','href'=>$url_personalizada,'meta'=>array(),));$slug_personalizado='wp-admin/network/admin.php?page=wppusher-plugins-create';$url_personalizada=home_url($slug_personalizado);$barra_admin->add_menu(array('id'=>'wp-pusher-install','title'=>'Instalar plugin','parent'=>'wp-pusher-main','href'=>$url_personalizada,'meta'=>array(),));$slug_personalizado='wp-admin/edit.php?post_type=bnfw_notification';$url_personalizada=home_url($slug_personalizado);$barra_admin->add_menu(array('id'=>'bnfw-tw','title'=>'Notifications for WP','parent'=>'herramientas-ind-tw','href'=>$url_personalizada,'meta'=>array(),));}},32,1);



        }else{
            if(!empty($license_key) && !empty($this->license_message)){
               $this->show_message=true;
            }
            update_option($license_key,"") || add_option($license_key,"");
            add_action( 'admin_post_KitFunnelLogin_el_activate_license', [ $this, 'action_activate_license' ] );
            add_action( 'admin_menu', [$this,'inactive_menu']);
        }
    }
    public function set_plugin_data(){
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		if ( function_exists( 'get_plugin_data' ) ) {
			$data = get_plugin_data( $this->plugin_file );
			if ( isset( $data['Version'] ) ) {
				$this->plugin_version = $data['Version'];
			}
			if ( isset( $data['TextDomain'] ) ) {
				$this->text_domain = $data['TextDomain'];
			}
		}
    }
	private static function &get_server_array() {
		return $_SERVER;
	}
	private static function get_raw_domain(){
		if(function_exists("site_url")){
			return site_url();
		}
		if ( defined( "WPINC" ) && function_exists( "get_bloginfo" ) ) {
			return get_bloginfo( 'url' );
		} else {
			$server = self::get_server_array();
			if ( ! empty( $server['HTTP_HOST'] ) && ! empty( $server['SCRIPT_NAME'] ) ) {
				$base_url  = ( ( isset( $server['HTTPS'] ) && $server['HTTPS'] == 'on' ) ? 'https' : 'http' );
				$base_url .= '://' . $server['HTTP_HOST'];
				$base_url .= str_replace( basename( $server['SCRIPT_NAME'] ), '', $server['SCRIPT_NAME'] );
				
				return $base_url;
			}
		}
		return '';
	}
	private static function get_raw_wp(){
		$domain=self::get_raw_domain();
		return preg_replace("(^https?://)", "", $domain );
	}
	public static function get_lic_key_param($key){
		$raw_url=self::get_raw_wp();
		return $key."_s".hash('crc32b',$raw_url."vtpbdapps");
	}
	public function set_admin_style() {
        wp_register_style( "KitFunnelLoginLic", plugins_url("_lic_style.css",$this->plugin_file),10,time());
        wp_enqueue_style( "KitFunnelLoginLic" );
    }
	public function active_admin_menu(){
        
		add_menu_page (  "KitFunnelLogin", "KitFunnel Login", "activate_plugins", $this->slug, [$this,"activated"], " dashicons-screenoptions ");
		//add_submenu_page(  $this->slug, "KitFunnelLogin License", "License Info", "activate_plugins",  $this->slug."_license", [$this,"activated"] );

    }
	public function inactive_menu() {
        add_menu_page( "KitFunnelLogin", "KitFunnel Login", 'activate_plugins', $this->slug,  [$this,"license_form"], " dashicons-screenoptions " );

    }
    function action_activate_license(){
        check_admin_referer( 'el-license' );
        $license_key=!empty($_POST['el_license_key'])?sanitize_text_field(wp_unslash($_POST['el_license_key'])):"";
        $license_email=!empty($_POST['el_license_email'])?sanitize_email(wp_unslash($_POST['el_license_email'])):"";
        update_option("KitFunnelLogin_lic_Key",$license_key) || add_option("KitFunnelLogin_lic_Key",$license_key);
        update_option("KitFunnelLogin_lic_email",$license_email) || add_option("KitFunnelLogin_lic_email",$license_email);
        update_option('_site_transient_update_plugins','');
        wp_safe_redirect(admin_url( 'admin.php?page='.$this->slug));
    }
    function action_deactivate_license() {
        check_admin_referer( 'el-license' );
        $message="";
	    $main_lic_key="KitFunnelLogin_lic_Key";
	    $lic_key_name =Kit_Funnel_Login_Base::get_lic_key_param($main_lic_key);
        if(Kit_Funnel_Login_Base::remove_license_key(__FILE__,$message)){
            update_option($lic_key_name,"") || add_option($lic_key_name,"");
            update_option('_site_transient_update_plugins','');
        }
        wp_safe_redirect(admin_url( 'admin.php?page='.$this->slug));
    }
    function activated(){
        ?>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="KitFunnelLogin_el_deactivate_license"/>
            <div class="el-license-container">
                <h3 class="el-license-title"><i class="dashicons-before dashicons-screenoptions"></i> <?php esc_html_e("KitFunnel Login","kitfunnel-login");?> </h3>
                <hr>
                <ul class="el-license-info">
                <li>
                    <div>
                        <span class="el-license-info-title"><?php esc_html_e("Status","kitfunnel-login");?></span>

                        <?php if ( $this->response_obj->is_valid ) : ?>
                            <span class="el-license-valid"><?php esc_html_e("Valid","kitfunnel-login");?></span>
                        <?php else : ?>
                            <span class="el-license-valid"><?php esc_html_e("Invalid","kitfunnel-login");?></span>
                        <?php endif; ?>
                    </div>
                </li>

                <li>
                    <div>
                        <span class="el-license-info-title"><?php esc_html_e("License Type","kitfunnel-login");?></span>
                        <?php echo esc_html($this->response_obj->license_title,"kitfunnel-login"); ?>
                    </div>
                </li>

               <li>
                   <div>
                       <span class="el-license-info-title"><?php esc_html_e("License Expired on","kitfunnel-login");?></span>
                       <?php echo esc_html($this->response_obj->expire_date,"kitfunnel-login");
                       if(!empty($this->response_obj->expire_renew_link)){
                           ?>
                           <a target="_blank" class="el-blue-btn" href="<?php echo esc_url($this->response_obj->expire_renew_link); ?>">Renew</a>
                           <?php
                       }
                       ?>
                   </div>
               </li>

               <li>
                   <div>
                       <span class="el-license-info-title"><?php esc_html_e("Support Expired on","kitfunnel-login");?></span>
                       <?php
                           echo esc_html($this->response_obj->support_end,"kitfunnel-login");;
                        if(!empty($this->response_obj->support_renew_link)){
                            ?>
                               <a target="_blank" class="el-blue-btn" href="<?php echo esc_url($this->response_obj->support_renew_link); ?>">Renew</a>
                            <?php
                        }
                       ?>
                   </div>
               </li>
                <li>
                    <div>
                        <span class="el-license-info-title"><?php esc_html_e("Your License Key","kitfunnel-login");?></span>
                        <span class="el-license-key"><?php echo esc_attr( substr($this->response_obj->license_key,0,9)."XXXXXXXX-XXXXXXXX".substr($this->response_obj->license_key,-9) ); ?></span>
                    </div>
                </li>
                </ul>
                <div class="el-license-active-btn">
                    <?php wp_nonce_field( 'el-license' ); ?>
                    <?php submit_button('Desactivar'); ?>
                </div>
            </div>
        </form>
    <?php
    }

    function license_form() {
        ?>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="KitFunnelLogin_el_activate_license"/>
        <div class="el-license-container">
            <h3 class="el-license-title"><i class="dashicons-before dashicons-screenoptions"></i> <?php esc_html_e("KitFunnel Login","kitfunnel-login");?></h3>
            <hr>
            <?php
            if(!empty($this->show_message) && !empty($this->license_message)){
                ?>
                <div class="notice notice-error is-dismissible">
                    <p><?php echo esc_html($this->license_message,"kitfunnel-login"); ?></p>
                </div>
                <?php
            }
            ?>
            <p><?php esc_html_e("Ingresa tu clave de licencia y correo electrónico de compra para activar [KitFunnel Login] y habilitar las actualizaciones.","kitfunnel-login");?></p>
            <p><br></p>

            <div class="el-license-field">
                <label for="el_license_key"><?php echo esc_html("Código de licencia","kitfunnel-login");?></label>
                <input type="text" class="regular-text code" name="el_license_key" size="50" placeholder="xxxxxxxx-xxxxxxxx-xxxxxxxx-xxxxxxxx" required="required">
            </div>
            <div class="el-license-field">
                <label for="el_license_key"><?php echo esc_html("Email","kitfunnel-login");?></label>
                <?php
                    $purchase_email   = get_option( "KitFunnelLogin_lic_email", get_bloginfo( 'admin_email' ));
                ?>
                <input type="text" class="regular-text code" name="el_license_email" size="50" value="<?php echo esc_html($purchase_email); ?>" placeholder="" required="required">
                <div><small><?php echo esc_html("Agrega el correo electrónico de registro cuando adquiriste KitFunnel.","kitfunnel-login");?></small></div>
            </div>
            <div class="el-license-active-btn">
                <?php wp_nonce_field( 'el-license' ); ?>
                <?php submit_button('Activar ahora'); ?>
            </div>
        </div>
    </form>
        <?php
    }
}

new KitFunnelLogin();