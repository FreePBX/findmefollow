<?php

namespace FreePBX\modules\Findmefollow\Api\Gql;

use GraphQLRelay\Relay;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\EnumType;
use FreePBX\modules\Api\Gql\Base;

/*
Examples
---------------------

mutation {
  enableFollowMe(input: {
    extensionId: "101"
  }) { message status }
}

mutation {
  disableFollowMe(input: {
    extensionId: "101"
  }) { message status }
}

** NOTE ** You can set a value for EITHER calendar or calendarGroup, not both.

mutation {
  updateFollowMe(input: {
    extensionId: "101"
    enabled: true
    followMeList: "101-103-2317094162#"
    strategy: ringallv2prim
    externalCallerIdMode: forcedid
    ringTime: 25
    followMePrefix: "Sales:"
    fixedCallerId: "2317094162"
    enableCalendar: false
    matchCalendar: true
    calendar: ""
    calendarGroup: "c6f2fb7f-55f5-419c-8657-e7aa3c6d6695"
    callerMessage: "1"
    noAnswerDestination: "ext-local,101,dest"
    alertInfo: ""
    confirmCalls: false
    receiverMessageConfirmCall: "1"
    receiverMessageTooLate: "1"
    ringingMusic: "Ring"
    initialRingTime: 7
    overrideRingerVolume: 0
  }) { message status }
}

query {
  fetchFollowMe(extensionId: "101") {
    id
    message
    status
    enabled
    extensionId
    strategy
    ringTime
    followMePrefix
    followMeList
    callerMessage
    noAnswerDestination
    alertInfo
    confirmCalls
    receiverMessageConfirmCall
    receiverMessageTooLate
    ringingMusic
    initialRingTime
    voicemail
    enableCalendar
    calendar
    calendarGroup
    matchCalendar
    overrideRingerVolume
    externalCallerIdMode
    fixedCallerId
  }
}
*/


/**
 * Find Me Follow GraphQL
 * 
 * Mutations
 * ---------------------
 * updateFollowMe(input: updateFollowMeInput): updateFollowMePayload
 * enableFollowMe(input: enableFollowMeInput): enableFollowMePayload
 * disableFollowMe(input: disableFollowMeInput): disableFollowMePayload
 * 
 * Queries
 * ---------------------
 * fetchFollowMe(extensionId: ID) : findmefollow
 * 
 */
class Findmefollow extends Base {
  protected $module = 'findmefollow';
  protected $ringStrategiesEnum = 'ringstrategiesv2';
  protected $externalCidModeEnum = 'externalcidmode';

  public function mutationCallback() {

    if (!$this->checkAllWriteScope()) {
      return;
    }

    return function() {
      return [
        'updateFollowMe' => $this->updateFollowMeMutation(),
        'enableFollowMe' => $this->enableFollowMeMutation(),
        'disableFollowMe' => $this->disableFollowMeMutation()
      ];
    };
  }

  public function queryCallback() {

    if (!$this->checkReadScope($this->module)) {
      return;
    }

    return function() {
      return [
        'fetchFollowMe' => [
          'type' => $this->typeContainer->get($this->module)->getObject(),
          'args' => [
            'extensionId' => [
              'type' => Type::nonNull(Type::id()),
              'description' => _('The extension number'),
            ]
          ],
          'resolve' => function($root, $args) {

            try {
            
              $extensionId = $args['extensionId'];
              $followMe = $this->freepbx->Findmefollow->get($extensionId, 1);

              if (!empty($followMe)) {

                $followMe['status'] = true;
                $followMe['message'] = 'Record found successfully';

                return $followMe;
              } else {
                return $this->generateOutput("No record found for $extensionId", false);
              }

            } catch (Exception $ex) {
              FormattedError::setInternalErrorMessage($ex->getMessage());
            }

          }]
      ];
    };
  }

  // This needs to be static so it's callable from within the fieldCallback configuration
  public static function hasValue($payload, $key) {
    return $payload && isset($payload[$key]) && !empty($payload[$key]);
  }

  // This needs to be static so it's callable from within the fieldCallback configuration
  public static function extractValue($payload, $key, $defaultValue = null) {
    return Findmefollow::hasValue($payload, $key) ? $payload[$key] : $defaultValue;
  }

  public function initializeTypes() {

    $this->initializeEnumTypes();

    $followMe = $this->typeContainer->create($this->module);
    $followMe->setDescription(_('Used to manage Follow Me'));

    $followMe->addInterfaceCallback(function() {
      return [$this->getNodeDefinition()['nodeInterface']];
    });

    $followMe->addFieldCallback(function() {
      return [
        'id' => Relay::globalIdField('extension', function($row) {
          return Findmefollow::extractValue($row, 'grpnum');
        }),
        'status' => [
          'type' => Type::boolean(),
          'description' => _('Status of the request')
        ],
        'message' => [
          'type' => Type::string(),
          'description' => _('Message for the request')
        ],
        'enabled' => [
          'type' => Type::nonNull(Type::boolean()), 
          'description' => _('If enabled, any call to this extension will go to this follow me instead, including directory calls by name from IVRs. If disabled, calls will go only to the extension. However, destinations that specify FollowMe will come here. This setting is often used in conjunction with VmX Locater, where you want a call to ring the extension, and then only if the caller chooses to find you do you want it to come here.'),
          'resolve' => function ($payload) {
            return Findmefollow::extractValue($payload, 'ddial') !== 'CHECKED';
          }
        ],
        'extensionId' => [
          'type' => Type::nonNull(Type::id()),
          'description' => _('Follow Me Extension Number'),
          'resolve' => function ($payload) {
            return Findmefollow::extractValue($payload, 'grpnum');
          }
        ],
        'strategy' => [
          'description' => _('Ring strategy for `followMeList`'),
          'type' => $this->typeContainer->get($this->ringStrategiesEnum)->getObject()
        ],
        'ringTime' => [
          'type' => Type::int(),
          'description' => _('Time in seconds that the phones will ring. Max 60 seconds'),
          'resolve' => function ($payload) {
            return Findmefollow::extractValue($payload, 'grptime');
          }
        ],
        'followMePrefix' => [
          'type' => Type::string(),
          'description' => _('CID Name Prefix like `Sales:`'),
          'resolve' => function ($payload) {
            return Findmefollow::extractValue($payload, 'grppre');
          }
        ],
        'followMeList' => [
          'type' => Type::string(),
          'description' => _('The numbers that will be dialed. Numbers are `-` separated. External numbers should have a `#` suffix.'),
          'resolve' => function ($payload) {
            return Findmefollow::extractValue($payload, 'grplist');
          }
        ],
        'callerMessage' => [
          'type' => Type::id(),
          'description' => _('System Recording ID. Announcement played to caller'),
          'resolve' => function ($payload) {
            return Findmefollow::extractValue($payload, 'annmsg_id');
          }
        ],
        'noAnswerDestination' => [
          'type' => Type::string(),
          'description' => _('Destination after `followMeList` is rung and there was no answer'),
          'resolve' => function ($payload) {
            return Findmefollow::extractValue($payload, 'postdest');
          }
        ],
        'alertInfo' => [
          'type' => Type::string(),
          'description' => _('Alert Info can be used for distinctive ring with SIP devices. If you are having issues, see the "Enforce RFC7462" option found in Settings > Advanced Settings.'),
          'resolve' => function ($payload) {
            return Findmefollow::extractValue($payload, 'dring');
          }
        ],
        'confirmCalls' => [
          'type' => Type::boolean(),
          'description' => _('Enable this if you are calling external numbers that need confirmation. Enabling this requires the remote side push 1 on their phone before the call is put through. This feature only works with the ringall ring strategy'),
          'resolve' => function ($payload) {
            return Findmefollow::extractValue($payload, 'needsconf');
          }
        ],
        'receiverMessageConfirmCall' => [
          'type' => Type::id(),
          'description' => _('System Recording ID. Message to be played to the person RECEIVING the call, if `confirmCalls` is enabled.'),
          'resolve' => function ($payload) {
            return Findmefollow::extractValue($payload, 'remotealert_id');
          }
        ],
        'receiverMessageTooLate' => [
          'type' => Type::id(),
          'description' => _('System Recording ID. Message to be played to the person RECEIVING the call, if the call has already been accepted before they push 1.'),
          'resolve' => function ($payload) {
            return Findmefollow::extractValue($payload, 'toolate_id');
          }
        ],
        'ringingMusic' => [
          'type' => Type::string(),
          'description' => _('Default `Ring`. Custom values will be the category name of "On Hold Music".'),
          'resolve' => function ($payload) {
            return Findmefollow::extractValue($payload, 'ringing');
          }
        ],
        'initialRingTime' => [
          'type' => Type::int(),
          'description' => _('Initial Ring Time. This is the number of seconds to ring the primary extension prior to proceeding to the `followMeList`. The extension can also be included in the `followMeList`. A 0 setting will bypass this.'),
          'resolve' => function ($payload) {
            return Findmefollow::extractValue($payload, 'pre_ring');
          }
        ],
        'voicemail' => [
          'type' => Type::string(),
          'description' => _('READONLY. If the value is `novm`, then there is no voicemail box for this extension.'),
          'resolve' => function ($payload) {
            return Findmefollow::extractValue($payload, 'voicemail');
          }
        ],
        'enableCalendar' => [
          'type' => Type::boolean(),
          'description' => _('Enable calendar for follow me (either group or single calendar)'),
          'resolve' => function ($payload) {
            return Findmefollow::extractValue($payload, 'calendar_enable');
          }
        ],
        'matchCalendar' => [
          'type' => Type::boolean(),
          'description' => _('This determines how matching an event is handled (`enableCalendar` must be true to activate). If true, then follow me will match whenever there is an event. If false, follow me will match whenever no event is present'),
          'resolve' => function ($payload) {
            return Findmefollow::extractValue($payload, 'calendar_match') === 'yes';
          }
        ],
        'calendar' => [
          'type' => Type::id(),
          'description' => _('Calendar Id'),
          'resolve' => function ($payload) {
            return Findmefollow::extractValue($payload, 'calendar_id');
          }
        ],
        'calendarGroup' => [
          'type' => Type::id(),
          'description' => _('Calendar Group Id'),
          'resolve' => function ($payload) {
            return Findmefollow::extractValue($payload, 'calendar_group_id');
          }
        ],
        'overrideRingerVolume' => [
          'type' => Type::int(),
          'description' => _('Ringer Volume Override. Note: This is only valid for Sangoma phones at this time'),
          'resolve' => function ($payload) {
            return Findmefollow::extractValue($payload, 'rvolume');
          }
        ],
        'externalCallerIdMode' => [
          'type' => $this->typeContainer->get($this->externalCidModeEnum)->getObject(),
          'description' => _('Choose the CID Mode from the `externalcidmode` enum. Default is `default`.'),
          'resolve' => function ($payload) {
            return Findmefollow::extractValue($payload, 'changecid', 'default');
          }
        ],
        'fixedCallerId' => [
          'type' => Type::string(),
          'description' => _('Fixed CID Value. Fixed value to replace the CID with used with `externalCallerIdMode` fixed modes. Should be in a format of digits only with an option of E164 format using a leading `+`.'),
          'resolve' => function ($payload) {
            return Findmefollow::extractValue($payload, 'fixedcid');
          }
        ]
      ];
    });
  }

  protected function initializeEnumTypes() {

    $strategyType = $this->typeContainer->create($this->ringStrategiesEnum, 'enum');
    $strategyType->setDescription('Ring Strategies (including v2)');
    $strategyType->addFields([
      'ringallv2' => [
        'value' => 'ringallv2',
        'description' => 'Ring Extension for duration set in Initial Ring Time, and then, while continuing call to extension (only if extension is in the Group List), ring `followMeList` for duration set in `ringTime`.'
      ],
      'ringallv2prim' => [
        'value' => 'ringallv2-prim',
        'description' => "Ring Extension for duration set in Initial Ring Time, and then, while continuing call to extension (only if extension is in the Group List), ring `followMeList` for duration set in `ringTime`. If the primary extension (first in list) is occupied, the other extensions will not be rung. If the primary is FreePBX DND, it won't be rung. If the primary is FreePBX CF unconditional, then all will be rung"
      ],
      'ringall' => [
        'value' => 'ringall',
        'description' => 'Ring all available channels until one answers (default)'
      ],
      'ringallprim' => [
        'value' => 'ringall-prim',
        'description' => "Ring all available channels until one answers. If the primary extension (first in list) is occupied, the other extensions will not be rung. If the primary is FreePBX DND, it won't be rung. If the primary is FreePBX CF unconditional, then all will be rung"
      ],
      'hunt' => [
        'value' => 'hunt',
        'description' => 'Take turns ringing each available extension'
      ],
      'huntprim' => [
        'value' => 'hunt-prim',
        'description' => "Take turns ringing each available extension. If the primary extension (first in list) is occupied, the other extensions will not be rung. If the primary is FreePBX DND, it won't be rung. If the primary is FreePBX CF unconditional, then all will be rung"
      ],
      'memoryhunt' => [
        'value' => 'memoryhunt',
        'description' => 'Ring first extension in the list, then ring the 1st and 2nd extension, then ring 1st 2nd and 3rd extension in the list.... etc'
      ],
      'memoryhuntprim' => [
        'value' => 'memoryhunt-prim',
        'description' => "Ring first extension in the list, then ring the 1st and 2nd extension, then ring 1st 2nd and 3rd extension in the list.... etc. If the primary extension (first in list) is occupied, the other extensions will not be rung. If the primary is FreePBX DND, it won't be rung. If the primary is FreePBX CF unconditional, then all will be rung"
      ],
      'firstavailable' => [
        'value' => 'firstavailable',
        'description' => 'Ring only the first available channel'
      ],
      'firstnotonphone' => [
        'value' => 'firstnotonphone',
        'description' => 'Ring only the first channel which is not offhook - ignore CW'
      ]
    ]);

    $externalCidModeType =  $this->typeContainer->create($this->externalCidModeEnum, 'enum');
    $externalCidModeType->setDescription('External Caller Id Modes');
    $externalCidModeType->addFields([
      'default' => [
        'value' => 'default',
        'description' => 'Default: Transmits the Callers CID if allowed by the trunk.'
      ],
      'fixed' => [
        'value' => 'fixed',
        'description' => 'Fixed CID Value: Always transmit the Fixed CID Value below.'
      ],
      'extern' => [
        'value' => 'extern',
        'description' => 'Outside Calls Fixed CID Value: Transmit the Fixed CID Value below on calls that come in from outside only. Internal extension to extension calls will continue to operate in default mode.'
      ],
      'did' => [
        'value' => 'did',
        'description' => 'Use Dialed Number: Transmit the number that was dialed as the CID for calls coming from outside. Internal extension to extension calls will continue to operate in default mode. There must be a DID on the inbound route for this. This will be BLOCKED on trunks that block foreign CallerID'
      ],
      'forcedid' => [
        'value' => 'forcedid',
        'description' => 'Force Dialed Number: Transmit the number that was dialed as the CID for calls coming from outside. Internal extension to extension calls will continue to operate in default mode. There must be a DID on the inbound route for this. This WILL be transmitted on trunks that block foreign CallerID'
      ]
    ]);
  }

  protected function updateFollowMeMutation() {

    $strategy = $this->typeContainer->get($this->ringStrategiesEnum)->getObject();
    $externalCid = $this->typeContainer->get($this->externalCidModeEnum)->getObject();

    $inputFields = [
      'extensionId' => [
        'type' => Type::nonNull(Type::id()),
        'description' => _('Follow Me Extension Number')
      ],
      'enabled' => [
        'type' => Type::nonNull(Type::boolean()), 
        'description' => _('If enabled, any call to this extension will go to this follow me instead, including directory calls by name from IVRs. If disabled, calls will go only to the extension. However, destinations that specify FollowMe will come here. This setting is often used in conjunction with VmX Locater, where you want a call to ring the extension, and then only if the caller chooses to find you do you want it to come here.')
      ],
      'strategy' => [
        'description' => _('Ring strategy for `followMeList`'),
        'type' => $strategy,
        'resolve' => function ($payload) {
          return $strategy->getValue($payload['strategy']);
        }
      ],
      'ringTime' => [
        'type' => Type::int(),
        'description' => _('Time in seconds that the phones will ring. Max 60 seconds')
      ],
      'followMePrefix' => [
        'type' => Type::string(),
        'description' => _('CID Name Prefix like `Sales:`')
      ],
      'followMeList' => [
        'type' => Type::string(),
        'description' => _('The numbers that will be dialed. Numbers are `-` separated. External numbers should have a `#` suffix.')
      ],
      'callerMessage' => [
        'type' => Type::id(),
        'description' => _('System Recording ID. Announcement played to caller')
      ],
      'noAnswerDestination' => [
        'type' => Type::string(),
        'description' => _('Destination after `followMeList` is rung and there was no answer')
      ],
      'alertInfo' => [
        'type' => Type::string(),
        'description' => _('Alert Info can be used for distinctive ring with SIP devices. If you are having issues, see the "Enforce RFC7462" option found in Settings > Advanced Settings.')
      ],
      'confirmCalls' => [
        'type' => Type::boolean(),
        'description' => _('Enable this if you are calling external numbers that need confirmation. Enabling this requires the remote side push 1 on their phone before the call is put through. This feature only works with the ringall ring strategy')
      ],
      'receiverMessageConfirmCall' => [
        'type' => Type::id(),
        'description' => _('System Recording ID. Message to be played to the person RECEIVING the call, if `confirmCalls` is enabled.')
      ],
      'receiverMessageTooLate' => [
        'type' => Type::id(),
        'description' => _('System Recording ID. Message to be played to the person RECEIVING the call, if the call has already been accepted before they push 1.')
      ],
      'ringingMusic' => [
        'type' => Type::string(),
        'description' => _('Default `Ring`. Custom values will be the category name of "On Hold Music".')
      ],
      'initialRingTime' => [
        'type' => Type::int(),
        'description' => _('Initial Ring Time. This is the number of seconds to ring the primary extension prior to proceeding to the `followMeList`. The extension can also be included in the `followMeList`. A 0 setting will bypass this.')
      ],
      'enableCalendar' => [
        'type' => Type::boolean(),
        'description' => _('Enable calendar for follow me (either group or single calendar)')
      ],
      'matchCalendar' => [
        'type' => Type::boolean(),
        'description' => _('This determines how matching an event is handled (`enableCalendar` must be true to activate). If true, then follow me will match whenever there is an event. If false, follow me will match whenever no event is present')
      ],
      'calendar' => [
        'type' => Type::id(),
        'description' => _('Calendar Id')
      ],
      'calendarGroup' => [
        'type' => Type::id(),
        'description' => _('Calendar Group Id')
      ],
      'overrideRingerVolume' => [
        'type' => Type::int(),
        'description' => _('Ringer Volume Override. Note: This is only valid for Sangoma phones at this time')
      ],
      'externalCallerIdMode' => [
        'description' => _('Choose the CID Mode from the `externalcidmode` enum. Default is `default`.'),
        'type' => $externalCid,
        'resolve' => function ($payload) {
          return $externalCid->getValue($payload['changecid']);
        }
      ],
      'fixedCallerId' => [
        'type' => Type::string(),
        'description' => _('Fixed CID Value. Fixed value to replace the CID with used with `externalCallerIdMode` fixed modes. Should be in a format of digits only with an option of E164 format using a leading `+`.')
      ]
    ];

    return $this->createMutation('updateFollowMe', 'Create/Update Follow Me', $inputFields, function ($input) {
      return $this->updateFollowMe($input);
    });
  }

  protected function enableFollowMeMutation() {

    $inputFields = [
      'extensionId' => [
        'type' => Type::nonNull(Type::id()),
        'description' => _('The Follow Me Extension Number')
      ]
    ];

    return $this->createMutation('enableFollowMe', 'Enable Follow Me', $inputFields, function ($input) {
      return $this->enableFollowMe($input);
    });
  }

  protected function disableFollowMeMutation() {

    $inputFields = [
      'extensionId' => [
        'type' => Type::nonNull(Type::id()),
        'description' => _('The Follow Me Extension Number')
      ]
    ];

    return $this->createMutation('disableFollowMe', 'Disable Follow Me', $inputFields, function ($input) {
      return $this->disableFollowMe($input);
    });
  }

  protected function createMutation($name, $description, $inputFields, $mutateAndGetPayload) {
    return Relay::mutationWithClientMutationId([
      'name' => $name,
      'description' => _($description),
      'inputFields' => $inputFields,
      'outputFields' => $this->getOutputFields(),
      'mutateAndGetPayload' => $mutateAndGetPayload
    ]);
  }

  protected function updateFollowMe($input) {

    if (!$this->isValidRoute($input)) {
      $destination = $input['noAnswerDestination'];
      return $this->generateOutput("Invalid Route: $destination", false);
    }
    $res = $this->freepbx->Core->getDevice($input['extensionId']);
    if(empty($res)){
      $message = _("This extension does not exist");
      return ['message' => $input['extensionId']." : ".$message,'status' => false];
    }
    $extensionId = $input['extensionId'];
    $input = $this->resolveInputNames($input);

    $status = $this->freepbx->Findmefollow->addSettingsById($extensionId, $input);
    $message = $status ? 'Follow me has been updated' : 'Sorry, follow me update failed';

    return $this->generateOutput($message, $status);
  }

  protected function isValidRoute($input) {
    
    // If the client hasn't requested to change the destination, then we'll assume it is accurate.
    if (!isset($input['noAnswerDestination'])) {
      return true;
    }

    $noAnswerDestination = $input['noAnswerDestination'];
    $destinations = $this->freepbx->Destinations->identifyDestinations([$noAnswerDestination]);

    // If identifyDestinations does not find the destination, then it will set that key as `false`
    $destination = isset($destinations[$noAnswerDestination]) ? $destinations[$noAnswerDestination] : false;

    $isValid =  $destination !== false;
    return $isValid;
  }
  
  protected function enableFollowMe($input) {

    $status = $this->freepbx->Findmefollow->addSettingById($input['extensionId'], 'ddial', '');
    $message = $status ? 'Follow me has been enabled' : 'Sorry, follow me failed to enable';
    
    return $this->generateOutput($message, $status);
  }

  protected function disableFollowMe($input) {

    $status = $this->freepbx->Findmefollow->addSettingById($input['extensionId'], 'ddial', 'CHECKED');
    $message = $status ? 'Follow me has been disabled' : 'Sorry, follow me failed to disable';
    
    return $this->generateOutput($message, $status);
  }
  
  protected function resolveInputNames($input) {

    // Mutate the values for customized fields
    if (isset($input['enabled'])) {
      $input['enabled'] = $input['enabled'] !== true ? 'CHECKED' : '';
    }

    if (isset($input['matchCalendar'])) {
      $input['matchCalendar'] = $input['matchCalendar'] === true ? 'yes' : 'no';
    }

    // `addSettingsById` expects the grplist to be an array
    if (Findmefollow::hasValue($input, 'followMeList')) {
      $input['followMeList'] = explode('-', $input['followMeList']);
    }

    // You can not set both a group and a calendar
    // You can however, switch the values by having one of the keys be an empty string ""
    // See the example mutation below:
    // 
    // mutation {
    //   updateFollowMe(input: {
    //     extensionId: "101"
    //     calendar: ""
    //     calendarGroup: "c6f2fb7f-55f5-419c-8657-e7aa3c6d6695"
    //   }) { message status }
    // }
    // 
    if (Findmefollow::hasValue($input, 'calendar') && Findmefollow::hasValue($input, 'calendarGroup')) {
      throw new \Exception("You can not set both a group and a calendar");
    }

    // keyMap = [ graphQLKey => internalKey ]
    $keyMap = [
      'enabled' => 'ddial',
      'extensionId' => 'grpnum',
      'strategy' => 'strategy',
      'ringTime' => 'grptime',
      'followMePrefix' => 'grppre',
      'followMeList' => 'grplist',
      'callerMessage' => 'annmsg_id',
      'noAnswerDestination' => 'postdest',
      'alertInfo' => 'dring',
      'confirmCalls' => 'needsconf',
      'receiverMessageConfirmCall' => 'remotealert_id',
      'receiverMessageTooLate' => 'toolate_id',
      'ringingMusic' => 'ringing',
      'initialRingTime' => 'pre_ring',
      'enableCalendar' => 'calendar_enable',
      'matchCalendar' => 'calendar_match',
      'calendar' => 'calendar_id',
      'calendarGroup' => 'calendar_group_id',
      'overrideRingerVolume' => 'rvolume',
      'externalCallerIdMode' => 'changecid',
      'fixedCallerId' => 'fixedcid'
    ];

    return $this->mapKeys($input, $keyMap);
  }

  protected function mapKeys($input, $keyMap) {

    if (empty($input)) {
      return [];
    }

    $data = [];
    foreach ($keyMap as $graphQLKey => $internalKey) {

      // This specific graphQLKey is not present within the mutation request
      if (!isset($input[$graphQLKey])) {
        continue;
      }

      // Map the value to our internalKey
      $data[$internalKey] = $input[$graphQLKey];
    }

    return $data;
  }

  protected function generateOutput($message, $status) {
    return ['message' => _($message), 'status' => $status];
  }

  protected function getOutputFields() {
    return [
     'status' => [
       'type' => Type::boolean(),
       'description' => _('Status of the request')
       ],
     'message' => [
       'type' => Type::string(),
       'description' => _('Message for the request')
       ]
     ];
   }

}