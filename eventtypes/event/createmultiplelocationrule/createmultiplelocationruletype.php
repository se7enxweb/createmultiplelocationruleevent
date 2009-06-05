<?php
/*

Jean-Luc NGUYEN
Avril 2009
Noven.fr

*/

class CreateMultipleLocationRuleType extends eZWorkflowEventType
{
    const WORKFLOW_TYPE_STRING = "createmultiplelocationrule";

    function CreateMultipleLocationRuleType()
    {
        $this->eZWorkflowEventType( CreateMultipleLocationRuleType::WORKFLOW_TYPE_STRING, ezi18n( 'kernel/workflow/event', 'Create Multiple Location Rule' ) );
        $this->setTriggerTypes( array( 'content' => array( 'publish' => array( 'before' ) ) ) );
	}

    function execute( $process, $event )
    {

        $parameters = $process->attribute( 'parameter_list' );
		$objectID = $parameters['object_id'];

        $object = eZContentObject::fetch( $objectID );
        
        if ( is_object( $object ) )
        	$classID = $object->attribute( 'contentclass_id' );
    	
    	$locationsINI = eZINI::instance( 'locations.ini' );
    	
    	$nodeIDArray = $locationsINI->variable( 'LocationsSettings', 'LocationsArray' );
		$arr = array();
		
        $db = eZDB::instance();
        $db->begin();
        foreach ( array_keys( $nodeIDArray ) as $key )
        {
        	if ( $classID == $key ) 
        	{
        		$arr = explode( ';', $nodeIDArray[$key] );
        		
        		foreach ( $arr as $nodeID ) 
        		{
        			$arrNode = eZContentObjectTreeNode::fetch( $nodeID );
        			
        			if ( is_object( $arrNode ) ) 
        			{
        				$arrObject = $arrNode->attribute( 'object' );
        				$arrClass = $arrObject->attribute( 'content_class' );
        				$isContainer = $arrClass->attribute( 'is_container' );
        			        				
        				if ( $isContainer ) 
        				{
							// I create a new location only if this is a new created object
							// This can be commented
							if ( $object->attribute( 'current_version' ) == 1 ) {
								$newNodeID = $object->addLocation( $nodeID, false );
							}
        				}
        			}
        		}
        	}
        }
        $db->commit();

        return eZWorkflowType::STATUS_ACCEPTED;

    }

}

eZWorkflowEventType::registerEventType( CreateMultipleLocationRuleType::WORKFLOW_TYPE_STRING, 'CreateMultipleLocationRuleType' );
?>