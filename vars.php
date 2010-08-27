<?php
/**
 * Vars
 *
 * �������� ����������� ��������� ����������
 *
 * @version 2.01
 *
 * @copyright 2007, Eresus Group, http://eresus.ru/
 * @copyright 2010, ��� "��� �����", http://dvaslona.ru/
 * @license http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @author Mikhail Krasilnikov <mk@procreat.ru>
 *
 * ������ ��������� �������� ��������� ����������� ������������. ��
 * ������ �������������� �� �/��� �������������� � ������������ �
 * ��������� ������ 3 ���� �� ������ ������ � ��������� ����� �������
 * ������ ����������� ������������ �������� GNU, �������������� Free
 * Software Foundation.
 *
 * �� �������������� ��� ��������� � ������� �� ��, ��� ��� ����� ���
 * ��������, ������ �� ������������� �� ��� ������� ��������, � ���
 * ����� �������� ��������� ��������� ��� ������� � ����������� ���
 * ������������� � ���������� �����. ��� ��������� ����� ���������
 * ���������� ������������ �� ����������� ������������ ��������� GNU.
 *
 * �� ������ ���� �������� ����� ����������� ������������ ��������
 * GNU � ���� ����������. ���� �� �� �� ��������, �������� �������� ��
 * <http://www.gnu.org/licenses/>
 *
 * @package Vars
 *
 * $Id$
 */

/**
 * ����� �������
 * @package Vars
 */
class Vars extends Plugin
{
	/**
	 * ��������� ������ ����
	 * @var string
	 */
	public $kernel = '2.12';

	var $title = 'Vars';
	var $type = 'client,admin';
	var $version = '2.01a';
	var $description = '�������� ����������� ��������� ����������';
	var $settings = array(
			);
	var $table = array (
		'name' => 'vars',
		'key'=> 'name',
		'sortMode' => 'caption',
		'sortDesc' => false,
		'columns' => array(
			array('name' => 'name', 'caption' => '���', 'value' => '&#36;($(name))', 'macros' => true),
			array('name' => 'caption', 'caption' => '��������'),
			),
		'controls' => array (
			'delete' => '',
			'edit' => '',
		),
		'tabs' => array(
			'width'=>'180px',
			'items'=>array(
			 array('caption'=>strAdd, 'name'=>'action', 'value'=>'create')
			),
		),
		'sql' => "(
			`name` varchar(31) NOT NULL,
			`caption` varchar(63) NOT NULL,
			`value` text NOT NULL,
			PRIMARY KEY  (`name`)
		) TYPE=MyISAM;",
	);

	/**
	 * �����������
	 *
	 * @return TVars
	 */
	function __construct()
	{
		parent::__construct();
		$this->listenEvents('clientOnPageRender', 'adminOnMenuRender');
	}
	//-----------------------------------------------------------------------------

	/**
	 * ����������
	 *
	 * @return void
	 */
	private function insert()
	{
		global $Eresus;

		$item = array(
			'name' => arg('name', 'word'),
			'caption' => arg('caption', 'dbsafe'),
			'value' => arg('value', 'dbsafe'),
		);
		$Eresus->db->insert($this->table['name'], $item);
		HTTP::redirect(arg('submitURL'));
	}
	//-----------------------------------------------------------------------------

	/**
	 * ���������
	 *
	 * @return void
	 */
	private function update()
	{
		global $Eresus;

		$item = $Eresus->db->selectItem($this->table['name'], "`name`='".arg('update', 'word')."'");
		$item['name'] = arg('name', 'word');
		$item['caption'] = arg('caption', 'dbsafe');
		$item['value'] = arg('value', 'dbsafe');

		$Eresus->db->updateItem($this->table['name'], $item, "`name`='".arg('update', 'word')."'");
		HTTP::redirect(arg('submitURL'));
	}
	//-----------------------------------------------------------------------------

	/**
	 * ������ ����������
	 *
	 * @return string
	 */
	private function adminAddItem()
	{
		global $page;

		$form = array(
			'name' => 'AddForm',
			'caption' => strAdd,
			'width'=>'500px',
			'fields' => array (
				array ('type' => 'hidden', 'name' => 'action', 'value' => 'insert'),
				array ('type' => 'edit', 'name' => 'name', 'label' => '��� $(', 'width' => '200px',
					'maxlength' => '31', 'comment' => ')', 'pattern' => '/.+/',
					'errormsg' => '�� ������� ��� ����������'),
				array ('type' => 'edit', 'name' => 'caption', 'label' => '��������', 'width' => '100%',
					'maxlength' => '63', 'pattern' => '/.+/', 'errormsg' => '�� ������� �������� ����������'),
				array ('type' => 'memo', 'name' => 'value', 'label' => '��������', 'height' => '10'),
			),
			'buttons' => array('ok', 'cancel'),
		);

		$result = $page->renderForm($form);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ������ ���������
	 *
	 * @return string
	 */
	private function adminEditItem()
	{
		global $Eresus, $page;

		$item = $Eresus->db->selectItem($this->table['name'], "`name`='".arg('id', 'word')."'");
		$form = array(
			'name' => 'EditForm',
			'caption' => strEdit,
			'width' => '500px',
			'fields' => array (
				array ('type' => 'hidden', 'name' => 'update', 'value' => $item['name']),
				array ('type' => 'edit', 'name' => 'name', 'label' => '��� $(', 'width' => '200px',
					'maxlength' => '31', 'comment' => ')', 'pattern' => '/.+/',
					'errormsg' => '�� ������� ��� ����������'),
				array ('type' => 'edit', 'name' => 'caption', 'label' => '��������', 'width' => '100%',
					'maxlength' => '63', 'pattern' => '/.+/', 'errormsg' => '�� ������� �������� ����������'),
				array ('type' => 'memo', 'name' => 'value', 'label' => '��������', 'height' => '10'),
			),
			'buttons' => array('ok', 'apply', 'cancel'),
		);
		$result = $page->renderForm($form, $item);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ���������� �������� ����������
	 *
	 * @return string  HTML
	 */
	function adminRender()
	{
		global $page;

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
				$result = $page->renderTable($this->table);
		}
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 *
	 * @return string
	 */
	function clientOnPageRender($text)
	{
		global $Eresus;

		$items = $Eresus->db->select($this->table['name']);
		if (count($items)) foreach ($items as $item) {
			$text= str_replace('$('.$item['name'].')', $item['value'], $text);
		}
		return $text;
	}
	//-----------------------------------------------------------------------------

	/**
	 * @return void
	 */
	function adminOnMenuRender()
	{
		global $page;

		$page->addMenuItem('����������', array ("access"  => EDITOR, "link"  => $this->name,
			"caption"  => '����������', "hint"  => "���������� ���������� �����������"));
	}
	//-----------------------------------------------------------------------------

	function install()
	{
		$this->createTable($this->table);
		parent::install();
	}

	private function createTable($table)
	{
		global $Eresus;

		$Eresus->db->query('CREATE TABLE IF NOT EXISTS `'.$Eresus->db->prefix.$table['name'].'`'.$table['sql']);
	}
	//-----------------------------------------------------------------------------

	/**
	 * ������� ����������
	 *
	 * @param string $name  ��� ����������
	 */
	private function delete($name)
	{
		global $page;

		$item = $this->dbItem('', $name, 'name');
		if ($item)
		{
			$this->dbDelete('', $name, 'name');
		}
		else
		{
			ErrorMessage('���������� � ������ "' . $name . '" �� �������');
		}
		HTTP::redirect(str_replace('&amp;', '&', $page->url()));
	}
	//-----------------------------------------------------------------------------
}

