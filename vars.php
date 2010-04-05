<?php
/**
 * Vars
 *
 * Eresus 2
 *
 * �������� ����������� ��������� ����������
 *
 * @version 1.07
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
class TVars extends TListContentPlugin
{
	/**
	 * ��� �������
	 * @var string
	 */
	var $name = 'vars';

	/**
	 * ��������� ������ ����
	 * @var string
	 */
	public $kernel = '2.12b';

	var $title = 'Vars';
	var $type = 'client,admin';
	var $version = '1.07b';
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
		global $Eresus;

		parent::__construct();
		$Eresus->plugins->events['clientOnPageRender'][] = $this->name;
		$Eresus->plugins->events['adminOnMenuRender'][] = $this->name;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ����������
	 *
	 * @return void
	 */
	function insert()
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
	function update()
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
	function adminAddItem()
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
	function adminEditItem()
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
	 *
	 * @return void
	 */
	function adminRender()
	{
		return $this->adminRenderContent();
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
}

