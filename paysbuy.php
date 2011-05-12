<?php
// Based on PayPal 1.4 module
// 1.01 - Fixed: Developer email


class Paysbuy extends PaymentModule
{
	private	$_html = '';
	private $_postErrors = array();

	public function __construct()
	{
		$this->name = 'paysbuy';
		$this->tab = 'Payment';
		$this->version = '1.1';
		$this->date = '5 Auguest 2009';
		$this->developer = 'Mr.Warun Kietduriyakul';
		$this->developeremail = 'Warun.Kietduriyakul@jomyut.net';
		
		$this->currencies = true;
		$this->currencies_mode = 'radio';

        parent::__construct();

        /* The parent construct is required for translations */
		$this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('Paysbuy Secure Gateway');
        $this->description = $this->l('Accepts payments by Paysbuy Co.Ltd. (Thailand)');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');
	}

	public function getPaysbuyUrl()
	{
		return "https://www.paysbuy.com/paynow.aspx?c=true";
	}

	public function install()
	{
		if (!parent::install() OR 
			!Configuration::updateValue('PAYSBUY_EMAIL', 'email@company.com') OR
			!Configuration::updateValue('PAYSBUY_VISA','0') OR
			!Configuration::updateValue('PAYSBUY_AMEX','0') OR
			!Configuration::updateValue('PAYSBUY_CHARGE','0') OR
			
			!$this->registerHook('payment')
		
		
		)
			return false;
		return true;
	}

	public function uninstall()
	{
		if (!Configuration::deleteByName('PAYSBUY_EMAIL') OR !parent::uninstall())
			return false;
		return true;
	}

	public function getContent()
	{
		$this->_html = '<h2>Paysbuy Payment Module</h2>';
		if (isset($_POST['submitPaysbuy']))
		{
			if (empty($_POST['email']))
				$this->_postErrors[] = $this->l('Paysbuy E-mail address is required.');
			elseif (!Validate::isEmail($_POST['email']))
				$this->_postErrors[] = $this->l('It is not E-Mail Address format');
				
			if (!sizeof($this->_postErrors))
			{
				Configuration::updateValue('PAYSBUY_EMAIL', $_POST['email']);
				Configuration::updateValue('PAYSBUY_VISA', isset($_POST['accept_visa']) ? $_POST['accept_visa']:0 );
				Configuration::updateValue('PAYSBUY_AMEX', isset($_POST['accept_amex']) ? $_POST['accept_amex']:0);
				Configuration::updateValue('PAYSBUY_CHARGE', $_POST['charge']);
				$this->displayConf();
			}
			else
				$this->displayErrors();
		}

		$this->displayPaysbuy();
		$this->displayFormSettings();
		return $this->_html;
	}

	public function displayConf()
	{
		$this->_html .= '
		<div class="conf confirm">
			<img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />
			'.$this->l('Settings updated').'
		</div>';
	}

	public function displayErrors()
	{
		$nbErrors = sizeof($this->_postErrors);
		$this->_html .= '
		<div class="alert error">
			<h3>'.($nbErrors > 1 ? $this->l('There are') : $this->l('There is')).' '.$nbErrors.' '.($nbErrors > 1 ? $this->l('errors') : $this->l('error')).'</h3>
			<ol>';
		foreach ($this->_postErrors AS $error)
			$this->_html .= '<li>'.$error.'</li>';
		$this->_html .= '
			</ol>
		</div>';
	}
	
	
	public function displayPaysbuy()
	{
		$this->_html .= '
		<img src="../modules/paysbuy/paysbuy.gif" style="float:left; margin-right:15px;" />
		<b>'.$this->l('This is an unofficial Paysbuy payment gateway for Prestashop').'</b><br /><br />
		'.$this->l('Please read the information before take any usage.').'<br />
		'.$this->l('You MUST configure your Paysbuy account first before using this module.').'
		<br /><br /><br />';
	}

	public function displayFormSettings()
	{
		$conf = Configuration::getMultiple(array('PAYSBUY_EMAIL','PAYSBUY_AMEX','PAYSBUY_VISA','PAYSBUY_CHARGE'));
		// E-Mail Address
		$email = array_key_exists('email', $_POST) ? $_POST['email'] : (array_key_exists('PAYSBUY_EMAIL', $conf) ? $conf['PAYSBUY_EMAIL'] : '');
		$accept_amex = array_key_exists('accept_amex', $_POST) ? $_POST['accept_amex'] : (array_key_exists('PAYSBUY_AMEX', $conf) ? $conf['PAYSBUY_AMEX'] : '');
		$accept_visa = array_key_exists('accept_visa', $_POST) ? $_POST['accept_visa'] : (array_key_exists('PAYSBUY_VISA', $conf) ? $conf['PAYSBUY_VISA'] : '');
		
		$charge = array_key_exists('charge', $_POST) ? $_POST['charge'] : (array_key_exists('PAYSBUY_CHARGE', $conf) ? $conf['PAYSBUY_CHARGE'] : '0');
		
		$this->_html .= '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
		<fieldset>
			<legend><img src="../img/admin/contact.gif" />'.$this->l('Settings').'</legend>
			
			<label>'.$this->l('Paysbuy E-mail').'</label>
			<div class="margin-form">
				<input type="text" size="33" name="email" value="'.htmlentities($email, ENT_COMPAT, 'UTF-8').'" />
			</div>
			
			<legend><img src="../img/admin/contact.gif" />'.$this->l('ช่องทางรับชำระค่าสินค้า').'</legend><br />
			
			<label>'.$this->l('Paysbuy Account') .'</label>
			<div class="margin-form">
				รับเงินจากบัญชีเพย์สบาย. (บริษัทเพย์สบายจะชาร์ตค่าใช้จ่ายที่ 3.75% ของราคาเงินรวม
			</div>
			
			<label>'.$this->l('VISA and MasterCard*') .'</label>
			<div class="margin-form">
				<input type="checkbox" name="accept_visa" '. ($accept_visa ? 'checked':'') .' /> 
				บริษัทเพย์สบายจะชาร์ตค่าใช้จ่ายที่ 4% ของราคาเงินรวม<br />
				*ต้องเปิดใช้บริการ DirectPay กับทางบริษัทเพย์สบายให้เรียบร้อยก่อน
			</div>
			<label>'.$this->l('American Express*') .'</label>
			<div class="margin-form">
				<input type="checkbox" name="accept_amex" '. ($accept_amex ? 'checked ':'') .'/> 
				บริษัทเพย์สบายจะชาร์ตค่าใช้จ่ายที่ 4.815% ของราคาเงินรวม<br />
				*ต้องเปิดใช้บริการ DirectPay กับทางบริษัทเพย์สบายให้เรียบร้อยก่อน
			</div>
			<label>'.$this->l('Currency') .'</label>
			<div class="margin-form">
				THB (Thai Baht)
			</div>
			<label>' . $this->l('Extra Charge') . '</label>
			<div class="margin-form">
				<input type="text" name="charge" value="'. $charge .'" /> % (BETA)<br />
				**ในความเป็นจริง ทางบริษัทเพย์สบายไม่อนุญาตให้ชาร์ตค่าใช้จ่ายเพิ่มขึ้นในการชำระเงินด้วยบัตรเครดิต การใช้งานฟังก์ชั่นนี้<br />
				อาจมีความเสี่ยงต่อการถูกยกเลิกแอคเค้าท์โดยบริษัทเพย์สบาย ดังนั้น ก่อนใช้งานควรพิจารณาให้รอบคอบ (ค่าทั่วไปคือ 0%)<br />
				
			</div>

			<br />
			
			<center><input type="submit" name="submitPaysbuy" value="'.$this->l('Update settings').'" class="button" /></center>
		
		</fieldset>		
		</form><br /><br />
		<fieldset class="width3">
			<legend><img src="../img/admin/warning.gif" />'.$this->l('Information').'</legend>
			<b style="color: red;">'.$this->l('All PrestaShop currencies must be also configured</b> inside Profile > Financial Information > Currency balances').'<br />
		</fieldset>
		<br /> <br />
		<fieldset class="width3">
			<legend><img src="../img/admin/manufacturers.gif" />'.$this->l('General Information').'</legend>
			โมดูลเพย์สบายนี้ถูกพัฒนาขึ้นโดยนักพัฒนาอิสระซึ่งไม่มีความเกี่ยวข้องกับบริษัทเพย์สบายจำกัด การใช้งานโมดูลนี้
			ผู้ใช้งานยินดีรับความเสี่ยงต่อการทำงานผิดพลาดของโปรแกรม โดยมีสัญญาอนุญาตแบบ GNU/GPL. <br />
			Paysbuy Module of Prestashop is an unofficial package under GNU/GPL license.	There are no any warranty 
			from defects of this module. You must carry any risks by yourself.<br /> <br />
			
			เพย์สบายเป็นเครื่องหมายการค้าของบริษัทเพย์สบายจำกัด<br />
			Paysbuy was registered as trademark of Paysbay Co. Ltd. (Thailand).<br /><br />
			
			บริษัทเพย์สบายไม่มีส่วนเกี่ยวข้องกับการพัฒนาโมดูลนี้<br />
			Paysbuy Co. Ltd. has not involve in this module.<br /><br />
			
			การพัฒนาโมดูล ศึกษาจากสคริปต์ตัวอย่างและการนำไปใช้กับ WHMCS ของ Siambox.com<br />
			This module original based on Paysbuy demonstration script and siambox.com whmcs integration code.<br /><br />
			
			<label>Module Version</label>
			<div class="margin-form">' . $this->version . '<br /> </div>
			<label>Module Build</label>
			<div class="margin-form">' . $this->date . '<br /> </div>
			<label>Features List</label>
			<div class="margin-form">
			- Make a final validation of the transaction with Paysbuy server.<br />
			- Transaction is logging into an order message area for future investigation.<br />
			- Easy to configuration <br />
			- เพิ่มให้สามารกำหนด Charge Rate เพิ่มเติมได้<br />
			- [fixed] คำนวณราคาพร้อมค่าจัดส่ง<br />
			

			</div>
			
		</fieldset>
		<br /><br />		
		<fieldset class="width3">
			<legend><img src="../img/admin/nav-user.gif" />'.$this->l('Developer Information').'</legend>
			<label>นักพัฒนา</label>
			<div class="margin-form">วรุณ เกียรติดุริยกุล<br /> </div>
			<label>อีเมล์</label>
			<div class="margin-form">Warun.Kietduriyakul@jomyut.net <br />(ขออภัยครับ, ไม่ใช้ในการตอบคำถามผ่านอีเมล์)</div>
			<label>ทวิตเตอร์</label>
			<div class="margin-form">@<a href="http://twitter.com/scalopus">scalopus</a></div>
			<label>สงสัยและแนะนำ</label>
			<div class="margin-form">กรณีต้องการสอบถามและคำแนะนำ เชิญที่ <a href="http://board.jomyut.net/index.php?board=3.0">JOMYUT.NET</a> forum</div>
			<label>Feel Goooood!</label>
			
			<Form method="post" action="https://www.paysbuy.com/paynow.aspx?c=true">
			<div class="margin-form">ซื้อต้มยำกุ้งพร้อมข้าวเพื่อสนับสนุนให้คนเขียนมีข้าวกินหนึ่งมื้อ \( ^_^ )/ <br /> (รับบัตรเครดิต)<br /><br />
			
			<input type="Hidden" Name="psb" value="psb"/>
			<input Type="Hidden" Name="biz" value="warun.kietduriyakul@jomyut.net"/>
			<input Type="Hidden" Name="inv" value=""/>
			<input Type="Hidden" Name="itm" value="Paysbuy Module of Prestashop Donation"/>
			<input Type="text" Name="amt" value="49" size="4" /> บาท (THB) and click 
			<input Type="Hidden" Name="postURL" value=""/>
			<input type="image" src="https://www.paysbuy.com/imgs/S_click2buy.gif" border="0" name="submit" alt="Make it easier,PaySbuy - its fast,free and secure!"/>
			</Form >

			
			
		</fieldset>
		<br /><br />
		
		
		
		';
	}

	public function hookPayment($params)
	{
		global $smarty;

		$address = new Address(intval($params['cart']->id_address_invoice));
		$customer = new Customer(intval($params['cart']->id_customer));
		$business = Configuration::get('PAYSBUY_EMAIL');
		
		$currency = $this->getCurrency();

		if (!Validate::isEmail($business))
			return $this->l('Paysbuy error: (invalid or undefined business account email)');

		if (!Validate::isLoadedObject($address) OR !Validate::isLoadedObject($customer) OR !Validate::isLoadedObject($currency))
			return $this->l('Paysbuy error: (invalid address or customer)');
			
		$products = $params['cart']->getProducts();
		
		foreach ($products as $key => $product)
		{
			$products[$key]['name'] = str_replace('"', '\'', $product['name']);
			if (isset($product['attributes']))
				$products[$key]['attributes'] = str_replace('"', '\'', $product['attributes']);
			$products[$key]['name'] = htmlentities(utf8_decode($product['name']));
			$products[$key]['paysbuyAmount'] = number_format(Tools::convertPrice($product['price_wt'], $currency), 2, '.', '');
		}
		$ChargeMultiplier = (Configuration::get('PAYSBUY_CHARGE') + 100) / 100;
		$smarty->assign(array(
			'address' => $address,
			'country' => new Country(intval($address->id_country)),
			'customer' => $customer,
			'business' => $business,
			'currency' => $currency,
			'accept_visa' => Configuration::get('PAYSBUY_VISA'),
			'accept_amex' => Configuration::get('PAYSBUY_AMEX'),
			'paysbuyUrl' => $this->getPaysbuyUrl(),
			'amount' => number_format(Tools::convertPrice($params['cart']->getOrderTotal(true, 4) * $ChargeMultiplier , $currency), 2, '.', ''),
			'shipping' =>  number_format(Tools::convertPrice(($params['cart']->getOrderShippingCost() + $params['cart']->getOrderTotal(true, 6)), $currency), 2, '.', ''),
			'discounts' => $params['cart']->getDiscounts(),
			'products' => $products,
			'total' => number_format(Tools::convertPrice($params['cart']->getOrderTotal(true, 3)* $ChargeMultiplier , $currency), 2, '.', ''),
			'id_cart' => intval($params['cart']->id),
			'postUrl' => 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'order-confirmation.php?key='.$customer->secure_key.'&id_cart='.intval($params['cart']->id).'&id_module='.intval($this->id),
			'reqUrl' => 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/paysbuy/validation.php',
			'this_path' => $this->_path,
			'Charge' => Configuration::get('PAYSBUY_CHARGE'),
		));

		return $this->display(__FILE__, 'paysbuy.tpl');
    }
	
	public function getL($key)
	{
		$translations = array(
			'mc_gross' => $this->l('PaySbuy key \'mc_gross\' not specified, can\'t control amount paid.'),
			'payment_status' => $this->l('Paysbuy key \'payment_status\' not specified, can\'t control payment validity'),
			'payment' => $this->l('Payment: '),
			'custom' => $this->l('PaySbuy key \'custom\' not specified, can\'t rely to cart'),
			'txn_id' => $this->l('PaySbuy key \'txn_id\' not specified, transaction unknown'),
			'mc_currency' => $this->l('PaySbuy key \'mc_currency\' not specified, currency unknown'),
			'cart' => $this->l('Cart not found'),
			'order' => $this->l('Order has already been placed'),
			'transaction' => $this->l('PaySbuy Transaction ID: '),
			'verified' => $this->l('The PaySbuy transaction could not be VERIFIED.'),
			'connect' => $this->l('Problem connecting to the PaySbuy server.'),
			'nomethod' => $this->l('No communications transport available.'),
			'socketmethod' => $this->l('Verification failure (using fsockopen). Returned: '),
			'curlmethod' => $this->l('Verification failure (using cURL). Returned: '),
			'curlmethodfailed' => $this->l('Connection using cURL failed'),
		);
		return $translations[$key];
	}
}

?>
