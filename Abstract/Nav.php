<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

abstract class NavMenu_Abstract_Nav extends Widget_Abstract{
    
    
    public function select()
    {
        return $this->db->select()->from('table.options');
    }
    
    /**
     * 插入一条记录
     *
     * @access public
     * @param array $options 记录插入值
     * @return integer
     */
    public function insert(array $options)
    {
        return $this->db->query($this->db->insert('table.options')->rows($options));
    }

    /**
     * 更新记录
     *
     * @access public
     * @param array $options 记录更新值
     * @param Typecho_Db_Query $condition 更新条件
     * @return integer
     */
    public function update(array $options, Typecho_Db_Query $condition)
    {
        return $this->db->query($condition->update('table.options')->rows($options));
    }
    
    /**
     * 删除记录
     *
     * @access public
     * @param Typecho_Db_Query $condition 删除条件
     * @return integer
     */
    public function delete(Typecho_Db_Query $condition)
    {
        return $this->db->query($condition->delete('table.options'));
    }
    
    /**
     * 获取记录总数
     *
     * @access public
     * @param Typecho_Db_Query $condition 计算条件
     * @return integer
     */
    public function size(Typecho_Db_Query $condition)
    {
        return $this->db->fetchObject($condition->select(array('COUNT(name)' => 'num'))->from('table.options'))->num;
    }
}
