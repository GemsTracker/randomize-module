<?php

/**
 *
 * @package    GemsRandomizer
 * @subpackage Tracker
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2020, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 */

namespace GemsRandomizer\Tracker;

/**
 *
 * @package    GemsRandomizer
 * @subpackage Tracker
 * @license    New BSD License
 * @since      Class available since version 1.8.8
 */
class RandomizationAssignment 
{
    /**
     *
     * @var string The block id
     */
    protected $_blockId;
    
    /**
     *
     * @var array The gems token data
     */
    protected $_gemsData = array();

    /**
     *
     * @var \Zend_Db_Adapter_Abstract
     */
    protected $db;

    /**
     * @var bool 
     */
    public $exists = false;
    
    /**
     * RandomizationAssignment constructor.
     *
     * @param array|string $blockData
     * @param \Zend_Db_Adapter_Abstract $db
     */
    public function __construct($blockData, \Zend_Db_Adapter_Abstract $db)
    {
        $this->db = $db;
        
        if (is_array($blockData)) {
            $this->_gemsData = $blockData;
            if (isset($blockData['grb_block_id'])) {
                $this->_blockId = $blockData['grb_block_id'];
                $this->exists   = true;
            } 
        } else {
            $this->_blockId = $blockData;
            $this->refresh();
        }
    }

    /**
     * @return string
     */
    public function getBlockId()
    {
        return $this->_blockId;
    }

    /**
     * @return string
     */
    public function getValueLabel()
    {
        return $this->_gemsData['grv_value_label'];
    }

    /**
     * @return $this
     */
    public function refresh()
    {
        $select = $this->db->select();
        $select->from('gemsrnd__randomization_blocks')
            ->joinInner('gemsrnd__randomization_values', 'grb_value_id = grv_value_id')
            ->where('grb_block_id = ?');
        
        $this->_gemsData = $this->db->fetchRow($select, $this->_blockId);
        $this->exists    = (boolean) isset($this->_gemsData['grb_block_id']);
        
        return $this;
    }
}