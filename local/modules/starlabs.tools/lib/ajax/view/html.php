<?php
namespace Starlabs\Tools\Ajax\View;

/**
 * @todo Протестировать работу
 * HTML view
 */
class Html extends Prototype
{
	/**
	 * Создает новый MVC HTML view
	 *
	 * @param string $data HTML текст
	 */
	public function __construct($data = '')
	{
		$this->data = $data;
	}

	/**
	 * Отсылает http-заголовки для view
	 *
	 * @return void
	 */
	public function sendHeaders()
	{
		header('Content-type: text/html; charset=' . SITE_CHARSET);
	}

	/**
	 * Формирует view
	 *
	 * @return string
	 */
	public function render()
	{
		return is_array($this->data) ? implode('', $this->data) : $this->data;
	}
}
