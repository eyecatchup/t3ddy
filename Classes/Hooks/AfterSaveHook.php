<?php
namespace ArminVieweg\T3ddy\Hooks;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2016 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use ArminVieweg\T3ddy\Utilities\DatabaseUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;

/**
 * AfterSave Hook
 *
 * @package ArminVieweg\T3ddy
 */
class AfterSaveHook
{
    /**
     * After Database Operations Hook
     * to create the first t3ddy item automatically if enabled
     *
     * @param string $status
     * @param string $table
     * @param mixed $id
     * @param array $fieldArray
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $pObj
     * @return void
     */
    public function processDatamap_afterDatabaseOperations(
        $status,
        $table,
        $id,
        array $fieldArray,
        \TYPO3\CMS\Core\DataHandling\DataHandler $pObj
    ) {
        $uid = $this->getUid($id, $table, $status, $pObj);

        if ($table === 'tt_content' &&
            $status === 'new' &&
            $fieldArray['CType'] === 'gridelements_pi1' &&
            in_array($fieldArray['tx_gridelements_backend_layout'], array('t3ddy-tab-container', 't3ddy-accordion'))
        ) {
            $newT3ddyItemTitle = 'New Item';

            $pageTs = BackendUtility::getPagesTSconfig($fieldArray['pid']);
            if (isset($pageTs['tx_t3ddy.']['defaults.']['newT3ddyItemTitle'])) {
                $newT3ddyItemTitle = $pageTs['tx_t3ddy.']['defaults.']['newT3ddyItemTitle'];
            }

            // Create new child item
            DatabaseUtility::getDatabaseConnection()->exec_INSERTquery(
                'tt_content',
                array_merge($fieldArray, array(
                    'tx_gridelements_backend_layout' => 't3ddy-item',
                    'tx_gridelements_container' => $uid,
                    'tx_gridelements_columns' => '31337',
                    'sorting' => 32,
                    'pi_flexform' => '',
                    'colPos' => '-1',
                    'header' => $newT3ddyItemTitle
                ))
            );
        }
    }

    /**
     * Investigates the uid of entry
     *
     * @param $id
     * @param $status
     * @param $pObj
     *
     * @return int
     */
    protected function getUid($id, $table, $status, $pObj)
    {
        $uid = $id;
        if ($status === 'new') {
            if (!$pObj->substNEWwithIDs[$id]) {
                //postProcessFieldArray
                $uid = 0;
            } else {
                //afterDatabaseOperations
                $uid = $pObj->substNEWwithIDs[$id];
                if (isset($pObj->autoVersionIdMap[$table][$uid])) {
                    $uid = $pObj->autoVersionIdMap[$table][$uid];
                }
            }
        }
        return intval($uid);
    }
}
