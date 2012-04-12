<?php
/**
 * Vars
 *
 * Создание собственных текстовых переменных
 *
 * @version 3.00
 *
 * @copyright 2007, Eresus Group, http://eresus.ru/
 * @copyright 2010, ООО "Два слона", http://dvaslona.ru/
 * @license http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @author Mikhail Krasilnikov <mk@procreat.ru>
 *
 * Данная программа является свободным программным обеспечением. Вы
 * вправе распространять ее и/или модифицировать в соответствии с
 * условиями версии 3 либо по вашему выбору с условиями более поздней
 * версии Стандартной Общественной Лицензии GNU, опубликованной Free
 * Software Foundation.
 *
 * Мы распространяем эту программу в надежде на то, что она будет вам
 * полезной, однако НЕ ПРЕДОСТАВЛЯЕМ НА НЕЕ НИКАКИХ ГАРАНТИЙ, в том
 * числе ГАРАНТИИ ТОВАРНОГО СОСТОЯНИЯ ПРИ ПРОДАЖЕ и ПРИГОДНОСТИ ДЛЯ
 * ИСПОЛЬЗОВАНИЯ В КОНКРЕТНЫХ ЦЕЛЯХ. Для получения более подробной
 * информации ознакомьтесь со Стандартной Общественной Лицензией GNU.
 *
 * Вы должны были получить копию Стандартной Общественной Лицензии
 * GNU с этой программой. Если Вы ее не получили, смотрите документ на
 * <http://www.gnu.org/licenses/>
 *
 * @package Vars
 *
 * $Id$
 */

/**
 * Класс плагина
 * @package Vars
 */
class Vars extends Plugin
{
	/**
	 * Максимальный размер переменной
	 *
	 * @var int
	 */
	const MAX_VAR_SIZE = 65536;

	/**
	 * Требуемая версия ядра
	 * @var string
	 */
	public $kernel = '3.00b';

	/**
	 * Название
	 *
	 * @var string
	 */
	public $title = 'Переменные';

	/**
	 * Версия
	 *
	 * @var string
	 */
	public $version = '3.00a';

	/**
	 * Описание
	 *
	 * @var string
	 */
	public $description = 'Создание собственных текстовых переменных';

	/**
	 * Таблица
	 *
	 * @var array
	 */
	public $table = array (
		'name' => 'vars',
		'key'=> 'name',
		'sortMode' => 'caption',
		'sortDesc' => false,
		'columns' => array(
			array('name' => 'name', 'caption' => 'Имя', 'value' => '&#36;($(name))', 'macros' => true),
			array('name' => 'caption', 'caption' => 'Описание'),
			),
		'controls' => array (
			'delete' => '',
			'edit' => '',
		),
		'tabs' => array(
			'width'=>'180px',
			'items'=>array(
				array('caption' => 'Добавить', 'name'=>'action', 'value'=>'create')
			),
		)
	);

	/**
	 * Конструктор
	 *
	 * @return Vars
	 */
	public function __construct()
	{
		parent::__construct();
		$this->listenEvents('clientOnPageRender', 'adminOnMenuRender');
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает разметку интерфейса
	 *
	 * @return string  HTML
	 */
	public function adminRender()
	{
		$result = '';

		switch (true)
		{
			case !is_null(arg('update')):
				$this->update();
			break;

			case !is_null(arg('delete')):
				$this->delete(arg('delete', 'dbsafe'));
			break;

			case !is_null(arg('id')):
				$result = $this->adminEditItem();
			break;

			case !is_null(arg('action')):
				switch (arg('action'))
				{
					case 'create':
						$result = $this->adminAddItem();
					break;

					case 'insert':
						$this->insert();
					break;
				}
			break;

			default:
				$result = $GLOBALS['page']->renderTable($this->table);
			break;
		}
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Производит подстановку переменных
	 *
	 * @param string $text  разметка страницы
	 *
	 * @return string  HTML
	 */
	public function clientOnPageRender($text)
	{
		$items = $this->dbSelect('');
		if (count($items))
		{
			foreach ($items as $item)
			{
				$text= str_replace('$(' . $item['name'] . ')', $item['value'], $text);
			}
		}
		return $text;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Добавляет пункт «Переменные» в меню «Расширения»
	 *
	 * @return void
	 */
	function adminOnMenuRender()
	{
		$GLOBALS['page']->addMenuItem('Расширения', array(
			'access' => EDITOR,
			'link' => $this->name,
			'caption' => 'Переменные',
			'hint'  => 'Управление текстовыми переменными'
		));
	}
	//-----------------------------------------------------------------------------

	/**
	 * @see main/core/Plugin::install()
	 */
	public function install()
	{
		parent::install();

		$sql = "
			`name` varchar(31) NOT NULL,
			`caption` varchar(63) NOT NULL,
			`value` text NOT NULL,
			PRIMARY KEY  (`name`)
		";

		$this->dbCreateTable($sql, '');
	}
	//-----------------------------------------------------------------------------

	/**
	 * Добавляет переменную в БД
	 *
	 * @return void
	 */
	private function insert()
	{
		$item = array(
			'name' => arg('name', 'word'),
			'caption' => arg('caption', 'dbsafe'),
			'value' => arg('value', 'dbsafe'),
		);
		// Считаем количество байтов, а не символов
		if (strlen($item['value']) > self::MAX_VAR_SIZE)
		{
			ErrorMessage('Размер переменной не должен превышать ' . self::MAX_VAR_SIZE . ' байт.');
			HTTP::goback();
		}
		$tmp = $this->dbItem('', $item['name'], 'name');
		if (!$tmp)
		{
			$this->dbInsert('', $item, 'name');
		}
		else
		{
			ErrorMessage('Переменная с именем "' . $item['name'] .
				'" уже существует. Выберите другое имя.');
			HTTP::goback();
		}

		HTTP::redirect(arg('submitURL'));
	}
	//-----------------------------------------------------------------------------

	/**
	 * Изменяет переменную в БД
	 *
	 * @return void
	 */
	private function update()
	{
		$oldName = arg('update', 'word');
		$item = $this->dbItem('', $oldName, 'name');
		// Считаем количество байтов, а не символов
		if (strlen($item['value']) > self::MAX_VAR_SIZE)
		{
			ErrorMessage('Размер переменной не должен превышать ' . self::MAX_VAR_SIZE . ' байт.');
			HTTP::goback();
		}

		$item['name'] = arg('name', 'word');
		$item['caption'] = arg('caption', 'dbsafe');
		$item['value'] = arg('value', 'dbsafe');
		if ($item['name'] != $oldName)
		{
			$tmp = $this->dbItem('', $item['name'], 'name');
			if ($tmp)
			{
				ErrorMessage('Переменная с именем "' . $item['name'] .
					'" уже существует. Выберите другое имя.');
				HTTP::redirect(arg('submitURL'));
			}
		}

		$q = DB::getHandler()->createUpdateQuery();
		$q->update($this->__table(''))
			->where($q->expr->eq('name', $q->bindValue($oldName, null, PDO::PARAM_STR)));

		foreach ($item as $key => $value)
		{
			$q->set($key, $q->bindValue($value));
		}

		DB::execute($q);

		$url = arg('submitURL');

		if ($item['name'] != $oldName)
		{
			$url = str_replace('id=' . $oldName, 'id=' . $item['name'], $url);
		}

		HTTP::redirect($url);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Диалог добавления
	 *
	 * @return string
	 */
	private function adminAddItem()
	{
		$form = array(
			'name' => 'AddForm',
			'caption' => 'Добавление переменной',
			'width'=>'500px',
			'fields' => array (
				array ('type' => 'hidden', 'name' => 'action', 'value' => 'insert'),
				array ('type' => 'edit', 'name' => 'name', 'label' => 'Имя $(', 'width' => '200px',
					'maxlength' => '31', 'comment' => ')', 'pattern' => '/[A-Za-z0-9_-]+/',
					'errormsg' => 'Имя переменной не указано или содержит недопустимые символы'),
				array ('type' => 'edit', 'name' => 'caption', 'label' => 'Описание', 'width' => '100%',
					'maxlength' => '63', 'pattern' => '/.+/', 'errormsg' => 'Не указано описание переменной'),
				array ('type' => 'memo', 'name' => 'value', 'label' => 'Значение', 'height' => '10'),
			),
			'buttons' => array('ok', 'cancel'),
		);

		$result = $GLOBALS['page']->renderForm($form);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Диалог изменения
	 *
	 * @return string
	 */
	private function adminEditItem()
	{
		$item = $this->dbItem('', arg('id', 'word'), 'name');
		$form = array(
			'name' => 'EditForm',
			'caption' => 'Изменение переменной',
			'width' => '500px',
			'fields' => array (
				array ('type' => 'hidden', 'name' => 'update', 'value' => $item['name']),
				array ('type' => 'edit', 'name' => 'name', 'label' => 'Имя $(', 'width' => '200px',
					'maxlength' => '31', 'comment' => ')', 'pattern' => '/[A-Za-z0-9_-]+/',
					'errormsg' => 'Имя переменной не указано или содержит недопустимые символы'),
				array ('type' => 'edit', 'name' => 'caption', 'label' => 'Описание', 'width' => '100%',
					'maxlength' => '63', 'pattern' => '/.+/', 'errormsg' => 'Не указано описание переменной'),
				array ('type' => 'memo', 'name' => 'value', 'label' => 'Значение', 'height' => '10'),
			),
			'buttons' => array('ok', 'apply', 'cancel'),
		);
		$result = $GLOBALS['page']->renderForm($form, $item);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Удаляет переменную
	 *
	 * @param string $name  Имя переменной
	 */
	private function delete($name)
	{
		$item = $this->dbItem('', $name, 'name');
		if ($item)
		{
			$this->dbDelete('', $name, 'name');
		}
		else
		{
			ErrorMessage('Переменной с именем "' . $name . '" не найдено.');
		}
		HTTP::redirect(str_replace('&amp;', '&', $GLOBALS['page']->url()));
	}
	//-----------------------------------------------------------------------------
}
