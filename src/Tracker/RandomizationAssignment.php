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

use Gems\Db\ResultFetcher;

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
    protected string $_blockId;
    
    /**
     *
     * @var array The gems token data
     */
    protected array $_gemsData = [];

    /**
     * @var bool 
     */
    public bool $exists = false;
    
    /**
     * RandomizationAssignment constructor.
     *
     * @param array|string $blockData
     * @param ResultFetcher $resultFetcher
     */
    public function __construct(
        array|string $blockData,
        protected readonly ResultFetcher $resultFetcher)
    {
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
    public function getBlockId(): string
    {
        return $this->_blockId;
    }

    /**
     * @return string
     */
    public function getValueLabel(): string
    {
        return $this->_gemsData['grv_value_label'];
    }

    /**
     * @return $this
     */
    public function refresh(): self
    {
        $select = $this->resultFetcher->getSelect();
        $select->from('gemsrnd__randomization_blocks')
            ->join('gemsrnd__randomization_values', 'grb_value_id = grv_value_id')
            ->where(['grb_block_id' =>  $this->_blockId]);
        
        $this->_gemsData = $this->resultFetcher->fetchRow($select);
        $this->exists    = (boolean) isset($this->_gemsData['grb_block_id']);
        
        return $this;
    }
}