<?php
defined('_MCSHOP') or die("Security block!");

class Main extends aDisplayable
{
	public function prepareDisplay()
	{
		if ($_GET['logoff']) {
			$_SESSION['Index']->user->Logout();
			setLocation('?show=Login&logoff=' . $_GET['logoff'], 'secure.' . BASE_DOMAIN);
		}

		$_SESSION['Index']->assign_say('MAIN_TITLE');

		if (!$_GET['content'] && !$_SESSION['content']) {
			$_SESSION['content'] = "{Itembox}"; #@todo: hier muss aus der Datenbank ausgelesen werden, welche Boxen beim ersten Aufruf der Seite angezeigt werden sollen
		}

		$templateId = 0;
		$this->customCss();
	}

	private function customCss()
	{
		$shopId = $_SESSION['Index']->shop->getId();
		$css = MySqlDatabase::getInstance()->query("SELECT Css FROM mc_customcss WHERE ShopId= :shopId AND TemplateId= :templateId LIMIT 1", array("shopId" => $shopId, "templateId" => $templateId));

		$_SESSION['Index']->assign('CUSTOMCSS', $css);
	}
}