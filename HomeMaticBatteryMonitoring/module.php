<?

########## HomeMatic Battery Monitoring Module ##########

/*
 * @file        module.php
 *
 * @author      Ulrich Bittner
 * @copyright   (c) 2018
 * @license     CC BY-NC-SA 4.0
 *
 * @version     1.00
 * @date:       2018-05-24, 19:45
 * @lastchange  2018-05-24, 19:45
 *
 * @see         https://https://github.com/ubittner/SymconHomeMaticBatteryMonitoring.git
 *
 * @guids       Library
 *              {2FF2A23B-D6BD-4474-ACBE-382773341175}
 *
 *              Module
 *              {AF3D2026-7739-4011-A0A4-B0A53F6556F8}
 *
 * @changelog   2018-05-24, 19:45, initial module script version 1.00
 *
 */


// Definitions
if (!defined('IPS_BASE')) {
    define("IPS_BASE", 10000);
}

if (!defined('IPS_KERNELMESSAGE')) {
    define("IPS_KERNELMESSAGE", IPS_BASE + 100);
}

if (!defined('KR_READY')) {
    define("KR_READY", IPS_BASE + 103);
}

if (!defined("HOMEMATIC_BATTERY_MONITORING_GUID")) {
    define("HOMEMATIC_BATTERY_MONITORING_GUID", "{AF3D2026-7739-4011-A0A4-B0A53F6556F8}");
}

if (!defined("HOMEMATIC_MODULE_GUID")) {
    define("HOMEMATIC_MODULE_GUID", "{EE4A81C6-5C90-4DB7-AD2F-F6BBD521412E}");
}

if (!defined("WEBFRONT_GUID")) {
    define("WEBFRONT_GUID", "{3565B1F2-8F7B-4311-A4B6-1BF1D868F39E}");
}

if (!defined("PUSHOVER_GUID")) {
    define("PUSHOVER_GUID", "{E38905D3-37E3-4AB3-861A-0F41CFE60BC8}");
}

if (!defined('MAIL_GUID')) {
    define("MAIL_GUID", "{375EAF21-35EF-4BC4-83B3-C780FD8BD88A}");
}


class HomeMaticBatteryMonitoring extends IPSModule
{
    public function Create()
    {
        // Never delete this line!
        parent::Create();
        // Register properties
        $this->RegisterPropertyInteger("CategoryID", 0);
        $this->RegisterPropertyString("InstanceDescription", $this->Translate("HomeMatic Battery Monitoring"));
        $this->RegisterPropertyString("LocationDesignation", "");
        $this->RegisterPropertyBoolean("UseDailyCheck", false);
        $this->RegisterPropertyString("DailyCheckTime", '{"hour":19,"minute":0,"second":0}');
        $this->RegisterPropertyString("TitleDescription", $this->Translate("Battery Monitoring"));
        $this->RegisterPropertyString("NotificationList", "");
        $this->RegisterPropertyString("EmailSubject", $this->Translate("Battery Monitoring"));
        $this->RegisterPropertyString("EmailRecipientList", "");
        // Register timer
        $this->RegisterTimer("CheckBatteryState", 0, 'UBHMBM_CheckBatteryState($_IPS[\'TARGET\']);');
    }


    public function ApplyChanges()
    {
        // Never delete this line!
        parent::ApplyChanges();
        // Register message
        $this->RegisterMessage(0, IPS_KERNELMESSAGE);
        if (IPS_GetKernelRunlevel() == KR_READY) {
            // Validate configuration
            $this->ValidateConfiguration();
            if (IPS_GetInstance($this->InstanceID)["InstanceStatus"] == 102) {
                // Set timer
                $this->SetCheckBatteryStateTimer();
                // Create links
                $this->CreateBatteryLinks();
            }
        }
    }


    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        // Log message
        switch ($Message) {
            case IPS_KERNELMESSAGE:
                if ($Data[0] == KR_READY) {
                    $this->ApplyChanges();
                }
                break;
        }
    }


    public function GetConfigurationForm()
    {
        $formdata = json_decode(file_get_contents(__DIR__ . "/form.json"));
        // Push notifications
        $notifications = json_decode($this->ReadPropertyString("NotificationList"));
        if (!empty($notifications)) {
            $notificationStatus = true;
            foreach ($notifications as $currentKey => $currentArray) {
                $rowColor = "";
                foreach ($notifications as $searchKey => $searchArray) {
                    // Search for double entries
                    if ($searchArray->Position == $currentArray->Position) {
                        if ($searchKey != $currentKey) {
                            $notificationStatus = false;
                        }
                    }
                    if ($searchArray->InstanceID == $currentArray->InstanceID) {
                        if ($searchKey != $currentKey) {

                            $notificationStatus = false;
                        }
                    }
                }
                // Check entries
                if ($currentArray->UseUtilisation == true) {
                    if ($currentArray->Position == "") {
                        $notificationStatus = false;
                    }

                    if ($currentArray->InstanceID == 0) {
                        $notificationStatus = false;
                    } else {
                        $guid = IPS_GetInstance($currentArray->InstanceID)['ModuleInfo']['ModuleID'];
                        if ($guid != WEBFRONT_GUID && $guid != PUSHOVER_GUID) {
                            $notificationStatus = false;
                        }
                    }
                    if ($notificationStatus == false) {
                        $rowColor = "#FFC0C0";
                        $this->SetStatus(2321);
                    }
                }
                $formdata->elements[13]->values[] = array("rowColor" => $rowColor);
            }
        }
        // Email notifications
        $emailRecipients = json_decode($this->ReadPropertyString("EmailRecipientList"));
        if (!empty($emailRecipients)) {
            $emailStatus = true;
            foreach ($emailRecipients as $currentKey => $currentArray) {
                $rowColor = "";
                foreach ($emailRecipients as $searchKey => $searchArray) {
                    // Search for double entries
                    if ($searchArray->Position == $currentArray->Position) {
                        if ($searchKey != $currentKey) {
                            $emailStatus = false;
                        }
                    }
                    if ($searchArray->EmailAddress == $currentArray->EmailAddress) {
                        if ($searchKey != $currentKey) {
                            $emailStatus = false;
                        }
                    }
                }
                // Check entries
                if ($currentArray->UseUtilisation == true) {
                    if ($currentArray->Position == "") {
                        $emailStatus = false;
                    }
                    if ($currentArray->InstanceID == 0) {
                        $emailStatus = false;
                    } else {
                        $guid = IPS_GetInstance($currentArray->InstanceID)['ModuleInfo']['ModuleID'];
                        if ($guid != MAIL_GUID) {
                            $emailStatus = false;
                        }
                    }
                    if (strlen($currentArray->EmailAddress) <= 1) {
                        $emailStatus = false;
                    }
                    if ($emailStatus == false) {
                        $rowColor = "#FFC0C0";
                        $this->SetStatus(2421);
                    }
                }
                $formdata->elements[16]->values[] = array("rowColor" => $rowColor);
            }
        }
        return json_encode($formdata);
    }


    #################### Public


    /**
     * Assigns the battery profile to all existing homematic lowbat objects
     */
    public function AssignBatteryProfile()
    {
        $instanceIDs = IPS_GetInstanceListByModuleID(HOMEMATIC_MODULE_GUID);
        if (!empty($instanceIDs)) {
            foreach ($instanceIDs as $instanceID) {
                $childrenIDs = IPS_GetChildrenIDs($instanceID);
                foreach ($childrenIDs as $childrenID) {
                    $object = IPS_GetObject($childrenID);
                    if ($object['ObjectIdent'] == "LOWBAT" || $object['ObjectIdent'] == "LOW_BAT") {
                        if ($object['ObjectType'] == 2) {
                            $variable = IPS_GetVariable($childrenID);
                            if ($variable['VariableType'] == 0) {
                                IPS_SetVariableCustomProfile($childrenID, "~Battery");
                            }
                        }
                    }
                }
            }
        }
    }


    /**
     * Shows the battery state of all existing homematic devices
     */
    public function ShowBatteryState()
    {
        // Get all instances of homematic devices and sort them by name
        $instanceIDs = IPS_GetInstanceListByModuleID(HOMEMATIC_MODULE_GUID);
        if (!empty($instanceIDs)) {
            // Sort devices by name
            $newInstanceIDs = array();
            foreach ($instanceIDs as $instanceID) {
                $childrenIDs = IPS_GetChildrenIDs($instanceID);
                foreach ($childrenIDs as $childrenID) {
                    $object = IPS_GetObject($childrenID);
                    if ($object['ObjectIdent'] == "LOWBAT" || $object['ObjectIdent'] == "LOW_BAT") {
                        $instanceName = IPS_GetName($instanceID);
                        array_push($newInstanceIDs, array("name" => $instanceName, "id" => $instanceID));
                    }
                }
            }
            sort($newInstanceIDs);
            // Show battery state for all homematic devices
            // Object ident is "LOWBAT" or "LOW_BAT"
            foreach ($newInstanceIDs as $key => $newInstanceID) {
                $instanceID = $newInstanceID["id"];
                $childrenIDs = IPS_GetChildrenIDs($instanceID);
                $instanceName = IPS_GetName($instanceID);
                $deviceAddress = IPS_GetProperty($instanceID, "Address");
                foreach ($childrenIDs as $childrenID) {
                    $object = IPS_GetObject($childrenID);
                    if ($object['ObjectIdent'] == "LOWBAT" || $object['ObjectIdent'] == "LOW_BAT") {
                        $batteryState = GetValue($childrenID);
                        if ($batteryState == false) {
                            $batteryStateFormatted = $this->Translate("OK");
                        } else {
                            $batteryStateFormatted = $this->Translate("Low Battery");
                        }
                        if ($batteryState == true) {
                            echo "------------------------------------------------------------------------------------------------------------------------\n";
                            echo $this->Translate("Low Battery") . ": \n";
                            echo "$instanceName, $deviceAddress," . $this->Translate("Battery State") . ": $batteryStateFormatted\n";
                            echo "------------------------------------------------------------------------------------------------------------------------\n";
                        } else {
                            echo "$instanceName, $deviceAddress, " . $this->Translate("Battery State") . ": $batteryStateFormatted\n";
                        }
                    }
                }
            }
        } else {
            echo $this->Translate("There are no HomeMatic devices available!");
        }
    }


    /**
     * Creates the battery links
     */
    public function CreateBatteryLinks()
    {
        // Get all existing homematic devices
        $targetIDs = array();
        $instanceIDs = IPS_GetInstanceListByModuleID(HOMEMATIC_MODULE_GUID);
        if (!empty($instanceIDs)) {
            $i = 0;
            foreach ($instanceIDs as $instanceID) {
                $childrenIDs = IPS_GetChildrenIDs($instanceID);
                foreach ($childrenIDs as $childrenID) {
                    $object = IPS_GetObject($childrenID);
                    if ($object['ObjectIdent'] == "LOWBAT" || $object['ObjectIdent'] == "LOW_BAT") {
                        $targetIDs[$i] = array("name" => IPS_GetName($instanceID), "targetID" => $childrenID);
                        $i++;
                    }
                }
            }
        }
        // Sort array alphabetically by device name
        sort($targetIDs);
        // Get all existing links
        $existingTargetIDs = array();
        $childrenIDs = IPS_GetChildrenIDs($this->InstanceID);
        $i = 0;
        foreach ($childrenIDs as $childrenID) {
            // Check if children is a link
            $objectType = IPS_GetObject($childrenID)["ObjectType"];
            if ($objectType == 6) {
                // Get target id
                $existingTargetID = IPS_GetLink($childrenID)["TargetID"];
                $existingTargetIDs[$i] = array("linkID" => $childrenID, "targetID" => $existingTargetID);
                $i++;
            }
        }
        // Delete dead links
        $deadLinks = array_diff(array_column($existingTargetIDs, 'targetID'), array_column($targetIDs, 'targetID'));
        if (!empty($deadLinks)) {
            foreach ($deadLinks as $targetID) {
                $position = array_search($targetID, array_column($existingTargetIDs, 'targetID'));
                $linkID = $existingTargetIDs[$position]["linkID"];
                if (IPS_LinkExists($linkID)) {
                    IPS_DeleteLink($linkID);
                }
            }
        }
        // Create new links
        $newLinks = array_diff(array_column($targetIDs, 'targetID'), array_column($existingTargetIDs, 'targetID'));
        if (!empty($newLinks)) {
            foreach ($newLinks as $targetID) {
                $linkID = IPS_CreateLink();
                IPS_SetParent($linkID, $this->InstanceID);
                $position = array_search($targetID, array_column($targetIDs, 'targetID'));
                IPS_SetPosition($linkID, $position);
                $name = $targetIDs[$position]['name'];
                IPS_SetName($linkID, $name);
                IPS_SetLinkTargetID($linkID, $targetID);
                IPS_SetIcon($linkID, "Battery");
            }
        }
        // Edit existing links
        $existingLinks = array_intersect(array_column($existingTargetIDs, 'targetID'), array_column($targetIDs, 'targetID'));
        if (!empty($existingLinks)) {
            foreach ($existingLinks as $targetID) {
                $position = array_search($targetID, array_column($targetIDs, 'targetID'));
                $targetID = $targetIDs[$position]['targetID'];
                $name = $targetIDs[$position]['name'];
                $index = array_search($targetID, array_column($existingTargetIDs, 'targetID'));
                $linkID = $existingTargetIDs[$index]["linkID"];
                IPS_SetPosition($linkID, $position);
                IPS_SetName($linkID, $name);
                IPS_SetIcon($linkID, "Battery");
            }
        }
    }


    /**
     * Checks the battery state of all existing homematic devices
     */
    public function CheckBatteryState()
    {
        $lowBattery = false;
        $notifications = json_decode($this->ReadPropertyString("NotificationList"));
        $emailRecipients = json_decode($this->ReadPropertyString("EmailRecipientList"));
        $title = $this->ReadPropertyString("TitleDescription");
        if ($title == "") {
            $title = $this->Translate("Battery Monitoring");
        }
        $location = $this->ReadPropertyString("LocationDesignation");
        // Check battery state of all existing homematic devices
        // Object ident is "LOWBAT" or "LOW_BAT"
        $instanceIDs = IPS_GetInstanceListByModuleID(HOMEMATIC_MODULE_GUID);
        if (!empty($instanceIDs)) {
            foreach ($instanceIDs as $instanceID) {
                $childrenIDs = IPS_GetChildrenIDs($instanceID);
                foreach ($childrenIDs as $childrenID) {
                    $object = IPS_GetObject($childrenID);
                    if ($object['ObjectIdent'] == "LOWBAT" || $object['ObjectIdent'] == "LOW_BAT") {
                        $batteryState = GetValue($childrenID);
                        if ($batteryState == true) {
                            $lowBattery = true;
                            $deviceName = IPS_GetName($instanceID);
                            $deviceAddress = IPS_GetProperty($instanceID, "Address");
                            // Low battery notification (alert notification)
                            if ($location != "") {
                                $preText = $this->Translate("Battery State") . " " . $location . ":\n";
                            } else {
                                $preText = $this->Translate("Battery State") . ":\n";
                            }
                            $alertText = $deviceName . "\n" . $deviceAddress . "\n" . $this->Translate("Battery low.") . "\n" . $this->Translate("Please replace as soon as possible!");
                            $text = $preText . $alertText;
                            IPS_LogMessage("UBHMBM", $title . "\n" . $text);
                            // Push notifications
                            if (!empty($notifications)) {
                                foreach ($notifications as $notification) {
                                    if ($notification->UseUtilisation == true && $notification->UseAlertNotification == true) {
                                        $moduleID = IPS_GetInstance($notification->InstanceID)['ModuleInfo']['ModuleID'];
                                        switch ($moduleID) {
                                            case WEBFRONT_GUID:
                                                $this->SendPushNotification($notification->InstanceID, $title, $text);
                                                break;
                                            case PUSHOVER_GUID:
                                                $this->SendPushoverNotification($notification->InstanceID, $title, $text);
                                                break;
                                        }
                                    }
                                }
                            }
                            // Email notifications
                            if (!empty($emailRecipients)) {
                                foreach ($emailRecipients as $emailRecipient) {
                                    if ($emailRecipient->UseUtilisation == true && $emailRecipient->UseAlertNotification == true) {
                                        $this->SendMailNotification($emailRecipient->InstanceID, $emailRecipient->EmailAddress, $text);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        // Battery state is ok
        // Push notifications
        if ($lowBattery == false) {
            if ($location != "") {
                $preText = $this->Translate("Battery State") . " " . $location . ": ";
            } else {
                $preText = $this->Translate("Battery State") . ": ";
            }
            $text = $preText . " OK";
            if (!empty($notifications)) {
                foreach ($notifications as $notification) {
                    if ($notification->UseUtilisation == true && $notification->UseNotification == true) {
                        $moduleID = IPS_GetInstance($notification->InstanceID)['ModuleInfo']['ModuleID'];
                        switch ($moduleID) {
                            case WEBFRONT_GUID:
                                $this->SendPushNotification($notification->InstanceID, $title, $text);
                                break;
                            case PUSHOVER_GUID:
                                $this->SendPushoverNotification($notification->InstanceID, $title, $text);
                                break;
                        }
                    }
                }
            }
            // EMail notifications
            if (!empty($emailRecipients)) {
                foreach ($emailRecipients as $emailRecipient) {
                    if ($emailRecipient->UseUtilisation == true && $emailRecipient->UseNotification == true) {
                        $this->SendMailNotification($emailRecipient->InstanceID, $emailRecipient->EmailAddress, $text);
                    }
                }
            }
        }
        $this->SetCheckBatteryStateTimer();
    }


    #################### Protected


    /**
     * Validates the configuration form
     */
    protected function ValidateConfiguration()
    {
        $this->SetStatus(102);
        // Check email subject
        $subject = $this->ReadPropertyString("EmailSubject");
        if ($subject == "") {
            $this->SetStatus(2411);
        }
        // Check title description
        $description = $this->ReadPropertyString("TitleDescription");
        if ($description == "") {
            $this->SetStatus(2311);
        }
        // Check description
        $description = $this->ReadPropertyString("InstanceDescription");
        if ($description == "") {
            $this->SetStatus(2121);
        } else {
            IPS_SetName($this->InstanceID, $description);
        }
        // Move instance to category
        $categoryID = $this->ReadPropertyInteger("CategoryID");
        IPS_SetParent($this->InstanceID, $categoryID);
    }


    /**
     * Sets the timer for battery state check
     */
    protected function SetCheckBatteryStateTimer()
    {
        $timerInterval = 0;
        if ($this->ReadPropertyBoolean("UseDailyCheck") == true) {
            $now = time();
            // Get time
            $dailyCheckTime = json_decode($this->ReadPropertyString("DailyCheckTime"));
            $hour = $dailyCheckTime->hour;
            $minute = $dailyCheckTime->minute;
            $second = $dailyCheckTime->second;
            $definedTime = $hour . ":" . $minute . ":" . $second;
            if (time() >= strtotime($definedTime)) {
                $timestamp = mktime($hour, $minute, $second, date('n'), date('j') + 1, date('Y'));
            } else {
                $timestamp = mktime($hour, $minute, $second, date('n'), date('j'), date('Y'));
            }
            $timerInterval = ($timestamp - $now) * 1000;
        }
        // Set timer
        $this->SetTimerInterval("CheckBatteryState", $timerInterval);
    }


    /**
     * Sends a message via push notification
     *
     * @param int    $WebFrontInstanceID
     * @param string $Title
     * @param string $Text
     */
    protected function SendPushNotification(int $WebFrontInstanceID, string $Title, string $Text)
    {
        $moduleID = IPS_GetInstance($WebFrontInstanceID)['ModuleInfo']['ModuleID'];
        if ($moduleID == WEBFRONT_GUID) {
            WFC_PushNotification($WebFrontInstanceID, $Title, $Text, "", 0);
        }
    }


    /**
     * Sends a message via Pushover
     *
     * @param int    $PushoverInstanceID
     * @param string $Title
     * @param string $Text
     */
    protected function SendPushoverNotification(int $PushoverInstanceID, string $Title, string $Text)
    {
        if (IPS_InstanceExists($PushoverInstanceID)) {
            $moduleID = IPS_GetInstance($PushoverInstanceID)['ModuleInfo']['ModuleID'];
            if ($moduleID == PUSHOVER_GUID) {
                UBPO_SendPushoverNotification($PushoverInstanceID, $Title, $Text);
            }
        }
    }


    /**
     * Sends a message via email
     *
     * @param int    $MailInstanceID
     * @param string $RecipientMailAddress
     * @param string $Text
     */
    private function SendMailNotification(int $MailInstanceID, string $RecipientMailAddress, string $Text)
    {
        if (IPS_InstanceExists($MailInstanceID)) {
            $moduleID = IPS_GetInstance($MailInstanceID)['ModuleInfo']['ModuleID'];
            if ($moduleID == MAIL_GUID) {
                $emailSubject = $this->ReadPropertyString("EmailSubject");
                SMTP_SendMailEx($MailInstanceID, $RecipientMailAddress, $emailSubject, $Text);
            }
        }
    }


}
