*** Settings ***
Library					ExtendedSelenium2Library
Library					String
Library					Collections
Library					json
#Resource				resource_basic_functions.robot
Resource				resource_helpers.robot
Resource				resource_navigations.robot
Resource				resource_templates.robot
Resource				resource_variables.robot

*** Keywords ***
#ORGANIZATIONS:
The Page Contains The Organization Table
	Wait Until Angular Ready
	Wait Until Page Contains Element	${ORG_TABLE_JQ}

The Rows of Organization Table Contains "${n}" Buttons
	Wait Until Angular Ready
	Wait Until Page Contains Element	${ORG_TABLE_ROW_BTNS_JQ}
	${e}=			Get Webelements		${ORG_TABLE_ROW_BTNS_JQ}
	${length}=		Get Length			${e}
	Should Be True	${length} == ${n}
	
The Settings Page Contains "${n}" Tabs
	Wait Until Angular Ready
	Wait Until Page Contains Element	${ORGANIZATIONS_NAV_TABS_JQ}
	${e}=			Get Webelements		${ORGANIZATIONS_NAV_TABS_JQ}
	${length}=		Get Length			${e}
	Should Be True	${length} == ${n}

#CONTENTS:
The Rows of Content List Contains Buttons
	${e}=			Get Webelements		${TABLE_ROW_BTNS}
	${length}=		Get Length			${e}
	Should Be True	${length} > 0

#GENERAL:
The Page Contains Table
	Wait Until Angular Ready
	Wait Until Page Contains Element	${TABLE}

The General Page contains the Expected Elements
	Wait Until Angular Ready
	Wait Until Page Contains Element	${GENERAL_ACTIVE_CHK_JQ}
	Page Should Contain Element			${GENERAL_ENG_NAME_JQ}
	Page Should Contain Element			${GENERAL_ENG_SH_DES_JQ}
	Page Should Contain Element			${GENERAL_ENG_LO_DES_TMCE_JQ}
	Page Should Contain Element			${GENERAL_SAVE_JQ}
	Page Should Contain Element			${GENERAL_CLEAR_JQ}

User Set General Hotel Activity; Is Active: "${active}"
	Wait Until Angular Ready
	Wait Until Page Contains Element		${GENERAL_ACTIVE_CHK_JQ}
	Run Keyword If		'${active}' == 'true'	Select Checkbox		${GENERAL_ACTIVE_CHK_JQ}
	...		ELSE IF		'${active}' == 'false'	Unselect Checkbox	${GENERAL_ACTIVE_CHK_JQ}
User Set General Hotel Name: "${hotel_name}"
	${list}=		Split String		${hotel_name}		;
	${eng}=			Remove From List	${list}		0
	${len}=			Get Length	${list}
	
	Wait Until Angular Ready
	Wait Until Page Contains Element	${GENERAL_ENG_NAME_JQ}
	Input Text							${GENERAL_ENG_NAME_JQ}	${eng}
	
	Run Keyword If		${len} > 0		User Set Translates to Block	Name		${list}
User Set General Hotel Short Description: "${short_desc}"
	${list}=		Split String		${short_desc}		;
	${eng}=			Remove From List	${list}		0
	${len}=			Get Length	${list}
	
	Wait Until Angular Ready
	Wait Until Page Contains Element	${GENERAL_ENG_SH_DES_JQ}
	Input Text							${GENERAL_ENG_SH_DES_JQ}	${eng}
	
	Run Keyword If		${len} > 0		User Set Translates to Block	Short description		${list}
User Set General Hotel Long Description: "${long_desc}"
	${list}=		Split String		${long_desc}		;
	${eng}=			Remove From List	${list}		0
	${len}=			Get Length	${list}

	Wait Until Angular Ready
	Wait Until Page Contains Element	${GENERAL_ENG_LO_DES_TMCE_JQ}
	Select Frame						${GENERAL_ENG_LO_DES_TMCE_JQ}
	Press Key							css=body		\\01
	Press Key							css=body		${eng}
	Unselect Frame
	
	Run Keyword If		${len} > 0		User Set Translates to Block TinyMCE	Long description		${list}
User Set General Hotel Parent Organization: "${parent_org}"
	Robot Helps Select an Option with Jquery 	${GENERAL_PARENT_SEL_JQ} 	${parent_org}
User Click General Save Button
	Wait Until Angular Ready
	Wait Until Page Contains Element		${GENERAL_SAVE_JQ}
	Click Button							${GENERAL_SAVE_JQ}
User Click General Clear Button
	Wait Until Angular Ready
	Wait Until Page Contains Element		${GENERAL_CLEAR_JQ}
	Click Button							${GENERAL_CLEAR_JQ}
The General Save Button is disabled
	#Robot Wait Attribute Value in Selector	${GENERAL_SAVE_JQ}	ng-reflect-disabled		true
	Run Keyword and Return Status
	...							Robot Wait Standalone Attribute in Selector					${GENERAL_SAVE_JQ}	disabled
The General Save Button is enabled
	#Robot Wait Selector Not Contain Attribute	${GENERAL_SAVE_JQ}	ng-reflect-disabled		false
	Run Keyword and Return Status
	...							Robot Wait Standalone Attribute Disappeared from Selector	${GENERAL_SAVE_JQ}	disabled
User Press Tab key on General Name Input
	Press Key			${GENERAL_ENG_NAME_JQ}	\\09
User Press Backspace key on General Name Input
	Robot Helps Pressing Backspace Key	${GENERAL_ENG_NAME_JQ}
User Press Tab key on General Short Description Input
	Press Key			${GENERAL_ENG_SH_DES_JQ}	\\09
User Press Backspace key on General Short Description Input
	Robot Helps Pressing Backspace Key	${GENERAL_ENG_SH_DES_JQ}
User Press Tab key on General Long Description Input
	Press Key			${GENERAL_ENG_LO_DES_JQ}	\\09
User Press Backspace key on General Long Description Input
	#Robot Helps Pressing Backspace Key	${GENERAL_ENG_LO_DES_JQ}
	Robot Helps Pressing Backspace Key on iframe	${GENERAL_ENG_LO_DES_TMCE_JQ}
	
#LOCATION:
The Location Page Contains the Expected Elements
	Wait Until Angular Ready
	Wait Until Page Contains Element	${LOCATION_ISLAND_SELECT_JQ}	10s
	Page Should Contain Element			${LOCATION_DISCRICT_SELECT_JQ}	
	Page Should Contain Element			${LOCATION_LATITUDE_JQ}			
	Page Should Contain Element			${LOCATION_LONGITUDE_JQ}		
	Page Should Contain Element			${LOCATION_PO_BOX_JQ}			
	Page Should Contain Element			${LOCATION_SAVE_BTN_JQ}
	
User Set Island: "${island}"
	Robot Helps Select an Option with Jquery	${LOCATION_ISLAND_SELECT_JQ}	${island}
User Set District: "${district}"
	Robot Helps Select an Option with Jquery	${LOCATION_DISCRICT_SELECT_JQ}	${district}
User Set Latitude: "${latitude}"
	Robot Helps Write to Input with Jquery		${LOCATION_LATITUDE_JQ}			${latitude}
User Set Longitude: "${longitude}"
	Robot Helps Write to Input with Jquery		${LOCATION_LONGITUDE_JQ}		${longitude}
User Set P.O. Box: "${po}"
	Robot Helps Write to Input with Jquery		${LOCATION_PO_BOX_JQ}			${po}
User Click Location Save Button
	Robot Helps Push The Button					${LOCATION_SAVE_BTN_JQ}
Robot Compare Location; Island: "${island}"
	Robot Helps Compare Selected Option			${LOCATION_ISLAND_SELECT_JQ}	${island}
Robot Compare Location; District: "${district}"
	Robot Helps Compare Selected Option			${LOCATION_DISCRICT_SELECT_JQ}	${district}
Robot Compare Location; Latitude: "${latitude}"
	Robot Helps Compare Input Value				${LOCATION_LATITUDE_JQ}			${latitude}
Robot Compare Location; Longitude: "${longitude}"
	Robot Helps Compare Input Value				${LOCATION_LONGITUDE_JQ}		${longitude}
Robot Compare Location; P.O. Box: "${po}"
	Robot Helps Compare Input Value				${LOCATION_PO_BOX_JQ}			${po}

#PROPERTIES:-------------------------------------------------------------------------------------------------PROPERTIES:
The Properties page contains expected elements
	Wait Until Angular Ready
	The Properties Page Contains "8" Tabs
	Robot Helps Check Properties Inner Tab		${PROP_TABS_BY_NAME}
#	Wait Until Angular Ready
#	Wait Until Page Contains Element	${PROPERTIES_NAME_TABLE_JQ}
#	Page Should Contain Element			${PROPERTIES_NAME_ENG_IN_JQ}
#	Page Should Contain Element			${PROPERTIES_NAME_ENG_ADD_JQ}
#	Page Should Contain Element			${PROPERTIES_CHARGE_TABLE_JQ}
#	Page Should Contain Element			${PROPERTIES_CHARGE_ENG_IN_JQ}
#	Page Should Contain Element			${PROPERTIES_CHARGE_ENG_ADD_JQ}
#	Page Should Contain Element			${PROPERTIES_HIGHLIGHT_SEL_JQ}
#	Page Should Contain Element			${PROPERTIES_LISTABLE_SEL_JQ}
#	Page Should Contain Element			${PROPERTIES_PRIORITY_IN_JQ}
#	Page Should Contain Element			${PROPERTIES_SAVE_BTN_JQ}
#	Page Should Contain Element			${PROPERTIES_CLEAR_BTN_JQ}
#	Page Should Contain Element			${PROPERTIES_TABLE_XP}
User Click "${li}" Property Inner Tab
	Wait Until Angular Ready
	${sel}=		Convert To String		jquery=.nav-tabs:eq(1) li a:contains("${li}")
	Wait Until Page Contains Element	${sel}
	Click Element						${sel}
The "${modal}" Modal Dialog is Active
	${sel}=		Robot Helps Find Selector	${modal}
	Robot Wait Attribute Value in Selector	${sel}	style	display: block;
The "${modal}" Modal Dialog is Inactive
	${sel}=		Robot Helps Find Selector	${modal}
	Robot Wait Attribute Value in Selector	${sel}	style	display: none;
The "${modal}" Modal Dialog Contains Expected Elements
	@{list}=	Robot Helps Listing the Expected Elements	${modal}
	:FOR	${ELEMENT}	IN	@{list}
    	\	Wait Until Page Contains Element	${ELEMENT}	2s

The New content Modal Dialog is Active
    Wait Until Angular Ready
    Robot Wait Attribute Value in Selector	jquery=#new-content-component	style	display: block;
#	Wait Until Page Contains Element	${MODAL_TITLE}
#	${e}=			Get Webelements		${PROP_TABS}
#	${length}=		Get Length			${e}
#	Should Be True	${length} == ${n}
	




The Add Name input is visible on page
	Wait Until Page Contains Element			${PROPERTIES_NAME_INPUT_JQ}
The Add Name input is not visible on page
	Wait Until Page Does Not Contain Element	${PROPERTIES_NAME_INPUT_JQ}
The Add Charge input is visible on page
	Wait Until Page Contains Element			${PROPERTIES_CHARGE_INPUT_JQ}
The Add Charge input is not visible on page
	Wait Until Page Does Not Contain Element	${PROPERTIES_CHARGE_INPUT_JQ}
The Add Name input is visible on Classification editor dialog
	Wait Until Page Contains Element			${CLASS_ED_NAME_INPUT_JQ}
The Add Name input is not visible on Classification editor dialog
	Wait Until Page Does Not Contain Element	${CLASS_ED_NAME_INPUT_JQ}
The Add Value input is visible on Classification editor dialog
	Wait Until Page Contains Element			${CLASS_ED_VALUE_INPUT_JQ}
The Add Value input is not visible on Classification editor dialog
	Wait Until Page Does Not Contain Element	${CLASS_ED_VALUE_INPUT_JQ}
The Add Charge input is visible on Classification editor dialog
	Wait Until Page Contains Element			${CLASS_ED_CHARGE_INPUT_JQ}
The Add Charge input is not visible on Classification editor dialog
	Wait Until Page Does Not Contain Element	${CLASS_ED_CHARGE_INPUT_JQ}
Robot Can't find "${name}" Named row in Child Class table
	Wait Until Angular Ready
	${sel}=			Convert To String			jquery=label:contains("Child classifications") tr:contains("${name}")
	Wait Until Page Does Not Contain Element	${sel}
Robot Can't find "${name}" Named row in Child Metas table
	Wait Until Angular Ready
	${sel}=			Convert To String			jquery=label:contains("Child metas") tr:contains("${name}")
	Wait Until Page Does Not Contain Element	${sel}

User Set New Property Name: "${row}"
	${list}=		Split String		${row}		;
	${eng}=			Remove From List	${list}		0
	${len}=			Get Length	${list}

	Wait Until Angular Ready
	${sel}=			Convert To String	${PROPERTIES_NAME_ENG_IN_JQ} option:contains("${eng}")
	${stat}=		Run Keyword And Return Status	Wait Until Page Contains Element	${sel}	2s
	Run Keyword If	'${stat}' == 'True'		User Set Property Name: "${row}"
	...		ELSE	User Add New Name: "${row}"
User Add New Name: "${row}"
	${list}=		Split String		${row}		;
	${eng}=			Remove From List	${list}		0
	${len}=			Get Length	${list}

	Wait Until Angular Ready
	Wait Until Page Contains Element	${PROPERTIES_NAME_ENG_ADD_JQ}
	Click Button						${PROPERTIES_NAME_ENG_ADD_JQ}
	Wait Until Page Contains Element	${PROPERTIES_NAME_INPUT_JQ}
	Wait Until Page Contains Element	${PROPERTIES_NAME_SET_JQ}
	Input Text							${PROPERTIES_NAME_INPUT_JQ}		${eng}
	Click Button						${PROPERTIES_NAME_SET_JQ}
	${sel}=			Convert To String	${PROPERTIES_NAME_ENG_IN_JQ} option:contains("${eng}")
	Wait Until Page Contains Element	${sel}
	
	Run Keyword If		${len} > 0		User Set Translates to Block	Name	${list}
	
User Set New Property Charge: "${charge}"
	${list}=		Split String		${charge}		;
	${eng}=			Remove From List	${list}		0
	${len}=			Get Length	${list}
	
	Wait Until Angular Ready
	${sel}=			Convert To String	${PROPERTIES_CHARGE_ENG_IN_JQ} option:contains("${eng}")
	${stat}=		Run Keyword And Return Status	Wait Until Page Contains Element	${sel}	2s
	Run Keyword If	'${stat}' == 'True'		User Set Property Charge: "${charge}"
	...		ELSE	User Add New Charge: "${charge}"
User Add New Charge: "${charge}"
	${list}=		Split String		${charge}		;
	${eng}=		Remove From List	${list}		0
	${len}=			Get Length	${list}
	
	Wait Until Angular Ready
	Wait Until Page Contains Element	${PROPERTIES_CHARGE_ENG_ADD_JQ}
	Click Button						${PROPERTIES_CHARGE_ENG_ADD_JQ}
	Wait Until Page Contains Element	${PROPERTIES_CHARGE_INPUT_JQ}
	Wait Until Page Contains Element	${PROPERTIES_CHARGE_SET_JQ}
	Input Text							${PROPERTIES_CHARGE_INPUT_JQ}	${eng}
	Click Button						${PROPERTIES_CHARGE_SET_JQ}
	${sel}=			Convert To String	${PROPERTIES_CHARGE_ENG_IN_JQ} option:contains("${eng}")
	Wait Until Page Contains Element	${sel}
	
	Run Keyword If		${len} > 0		User Set Translates to Block	Charge	${list}
	
User Set Property Name: "${name}"
	${list}=		Split String		${name}		;
	${eng}=			Remove From List	${list}		0
	${len}=			Get Length	${list}
	
	Wait Until Angular Ready
	Wait Until Page Contains Element	${PROPERTIES_NAME_ENG_IN_JQ}
	${sel}=			Convert To String	${PROPERTIES_NAME_ENG_IN_JQ} option:contains("${eng}")
	Click Element						${sel}

	Run Keyword If		${len} > 0		User Set Translates to Block	Name	${list}
	
User Set Property Charge: "${charge}"
	${list}=		Split String		${charge}		;
	${eng}=			Remove From List	${list}		0
	${len}=			Get Length	${list}
	
	Wait Until Angular Ready
	Wait Until Page Contains Element	${PROPERTIES_CHARGE_ENG_IN_JQ}
	${sel}=			Convert To String	${PROPERTIES_CHARGE_ENG_IN_JQ} option:contains("${eng}")
	Click Element						${sel}
	
	Run Keyword If		${len} > 0		User Set Translates to Block	Charge	${list}
User Set Property Highlight: "${hl}"
	Wait Until Angular Ready
	Wait Until Page Contains Element	${PROPERTIES_HIGHLIGHT_SEL_JQ}
	Run Keyword If	'${hl}' == 'true'	Select Checkbox			${PROPERTIES_HIGHLIGHT_SEL_JQ}
	Run Keyword If	'${hl}' == 'false'	Unselect Checkbox		${PROPERTIES_HIGHLIGHT_SEL_JQ}
User Set Property Listable: "${listable}"
	Wait Until Angular Ready
	Wait Until Page Contains Element	${PROPERTIES_LISTABLE_SEL_JQ}
	Run Keyword If	'${listable}' == 'true'	Select Checkbox			${PROPERTIES_LISTABLE_SEL_JQ}
	Run Keyword If	'${listable}' == 'false'	Unselect Checkbox		${PROPERTIES_LISTABLE_SEL_JQ}
User Set Property Priority: "${prio}"
	Wait Until Angular Ready
	Wait Until Page Contains Element	${PROPERTIES_PRIORITY_IN_JQ}
	Input Text							${PROPERTIES_PRIORITY_IN_JQ}	${prio}
Robot Check "${row}" Row Name Column of Priority Table value is "${name}"
	${sel}=			Convert To String	jquery=table:eq(2) tbody tr:contains("${row}") td:eq("0")
	Robot Waits Text Visible in Selector	${row}			${sel}
Robot Check "${row}" Row Highlight Column of Priority Table value is "${hl}"
	${sel}=			Convert To String	jquery=table:eq(2) tbody tr:contains("${row}") td:eq("1")
	Robot Waits Text Visible in Selector	${hl}			${sel}	
Robot Check "${row}" Row Listable Column of Priority Table value is "${listable}"
	${sel}=			Convert To String	jquery=table:eq(2) tbody tr:contains("${row}") td:eq("2")
	Robot Waits Text Visible in Selector	${listable}		${sel}
Robot Check "${row}" Row Priority Column of Priority Table value is "${prio}"
	${sel}=			Convert To String	jquery=table:eq(2) tbody tr:contains("${row}") td:eq("3")
	Robot Waits Text Visible in Selector	${prio}		${sel}
User Click Property Save Button
	Wait Until Angular Ready
	Wait until Page Contains Element	${PROPERTIES_SAVE_BTN_JQ}
	Click Button						${PROPERTIES_SAVE_BTN_JQ}
User Click Property Clear Button
	Wait Until Angular Ready
	Wait until Page Contains Element	${PROPERTIES_CLEAR_BTN_JQ}
	Click Button						${PROPERTIES_CLEAR_BTN_JQ}
The Property Save Button is disabled
	#Robot Wait Attribute Value in Selector	${PROPERTIES_SAVE_BTN_JQ}	ng-reflect-disabled		true
	Run Keyword and Return Status
	...					Robot Wait Standalone Attribute in Selector					${PROPERTIES_SAVE_BTN_JQ}	disabled
The Property Save Button is enabled
	#Robot Wait Selector Not Contain Attribute	${PROPERTIES_SAVE_BTN_JQ}	ng-reflect-disabled		false
	Run Keyword and Return Status
	...					Robot Wait Standalone Attribute Disappeared from Selector	${PROPERTIES_SAVE_BTN_JQ}	disabled
	
#CHILD CLASS:-----------------------------------------------------------------------------------------------CHILD CLASS:
User Set New Child Class Name: "${name}"
	${list}=		Split String		${name}		;
	${eng}=			Remove From List	${list}		0
	${len}=			Get Length	${list}
	
	Wait Until Angular Ready
	${sel}=			Convert To String	${CLASS_ED_NAME_ENG_IN_JQ} option:contains("${eng}")
	${stat}=		Run Keyword And Return Status	Wait Until Page Contains Element	${sel}	2s
	Run Keyword If	'${stat}' == 'True'		User Set Child Class Name: "${name}"
	...		ELSE	User Add New Child Class Name: "${name}"
	
User Set Empty Child Class Name
	Robot Helps Push The Button		${CLASS_ED_NAME_ENG_IN_JQ} option:eq(0)
	
User Add New Child Class Name: "${name}"
	${list}=		Split String		${name}		;
	${eng}=			Remove From List	${list}		0
	${len}=			Get Length	${list}
	

	Wait Until Angular Ready
	Wait Until Page Contains Element	${CLASS_ED_NAME_ENG_ADD_JQ}
	Click Button						${CLASS_ED_NAME_ENG_ADD_JQ}
	Wait Until Page Contains Element	${CLASS_ED_NAME_INPUT_JQ}
	Wait Until Page Contains Element	${CLASS_ED_NAME_SET_JQ}
	Input Text							${CLASS_ED_NAME_INPUT_JQ}		${eng}
	Click Button						${CLASS_ED_NAME_SET_JQ}
	${sel}=			Convert To String	${CLASS_ED_NAME_ENG_IN_JQ} option:contains("${eng}")
	Wait Until Page Contains Element	${sel}
	
	Run Keyword If		${len} > 0		User Set Translates to Block on Modal Dialog	Name	${list}
	
User Set Child Class Name: "${name}"
	${list}=		Split String		${name}		;
	${eng}=			Remove From List	${list}		0
	${len}=			Get Length	${list}
	
	Wait Until Angular Ready
	Wait Until Page Contains Element	${CLASS_ED_NAME_ENG_IN_JQ}
	${sel}=			Convert To String	${CLASS_ED_NAME_ENG_IN_JQ} option:contains("${eng}")
	Click Element						${sel}
	
	Run Keyword If		${len} > 0		User Set Translates to Block on Modal Dialog	Name	${list}

#CHILD CLASS: SEARCHABLE
User Set New Child Class Searchable: "${searchable}"
	Run Keyword If		'${searchable}' == 'true'	Robot Helps Select Checkbox		${CLASS_ED_SEARCHABLE_JQ}
	...		ELSE IF		'${searchable}' == 'false'	Robot Helps Unselect Checkbox	${CLASS_ED_SEARCHABLE_JQ}
#CHILD CLASS: NAME ICON
User Set Child Class Name Icon: "${name_icon}"
	Robot Helps Select an Option with Jquery	${CLASS_ED_NAME_ICON_SEL}	${name_icon}
#CHILD CLASS: VALUE
User Set New Child Class Value: "${value}"
	${eng}=		Robot Gets English Language from separated list		${value}
	Wait Until Angular Ready
	${sel}=			Convert To String	${CLASS_ED_VALUE_ENG_IN_JQ} option:contains("${eng}")
	${stat}=		Run Keyword And Return Status	Wait Until Page Contains Element	${sel}	2s
	Run Keyword If	'${stat}' == 'True'		User Set Child Class Value: "${value}"
	...		ELSE	User Add New Child Class Value: "${value}"
User Add New Child Class Value: "${value}"
	${list}=		Split String		${value}		;
	${eng}=			Remove From List	${list}		0
	${len}=			Get Length	${list}
	
	Wait Until Angular Ready
	Wait Until Page Contains Element	${CLASS_ED_VALUE_ENG_ADD_JQ}
	Click Button						${CLASS_ED_VALUE_ENG_ADD_JQ}
	Wait Until Page Contains Element	${CLASS_ED_VALUE_INPUT_JQ}
	Wait Until Page Contains Element	${CLASS_ED_VALUE_SET_JQ}
	Input Text							${CLASS_ED_VALUE_INPUT_JQ}		${eng}
	Click Button						${CLASS_ED_VALUE_SET_JQ}
	${sel}=			Convert To String	${CLASS_ED_VALUE_ENG_IN_JQ} option:contains("${eng}")
	Wait Until Page Contains Element	${sel}
	
	Run Keyword If		${len} > 0		User Set Translates to Block on Modal Dialog	Value	${list}
	
User Set Child Class Value: "${value}"
	${list}=		Split String		${value}		;
	${eng}=			Remove From List	${list}		0
	${len}=			Get Length	${list}
	
	Wait Until Angular Ready
	Wait Until Page Contains Element	${CLASS_ED_VALUE_ENG_IN_JQ}
	${sel}=			Convert To String	${CLASS_ED_VALUE_ENG_IN_JQ} option:contains("${eng}")
	Click Element						${sel}
	
	Run Keyword If		${len} > 0		User Set Translates to Block on Modal Dialog	Value	${list}
User Set Child Class Value Icon: "${value_icon}"
	Robot Helps Select an Option with Jquery	${CLASS_ED_VALUE_ICON_SEL}	${value_icon}
#CHILD CLASS: DESC
User Set Child Class Description: "${desc}"
	${list}=		Split String		${desc}		;
	${eng}=			Remove From List	${list}		0
	${len}=			Get Length	${list}
	
	Wait Until Angular Ready
	Wait Until Page Contains Element	${CLASS_ED_DESC_ENG_IN_JQ}
	Input Text							${CLASS_ED_DESC_ENG_IN_JQ}	${eng}
	Run Keyword If		${len} > 0		User Set Translates to Block on Modal Dialog TinyMCE	Description		${list}
User Set Child Class Description to TinyMCE: "${desc}"
	${list}=		Split String		${desc}		;
	${eng}=			Remove From List	${list}		0
	${len}=			Get Length	${list}
	
	#Wait Until Angular Ready
	#Wait Until Page Contains Element	${CLASS_ED_DESC_ENG_TMCE_JQ}
	#Input Text							${CLASS_ED_DESC_ENG_TMCE_JQ}	${eng}
	Robot Helps Write to TinyMCE		${CLASS_ED_DESC_ENG_TMCE_JQ}	${eng}
	Run Keyword If		${len} > 0		User Set Translates to Block on Modal Dialog TinyMCE	Description		${list}
#CHILD CLASS: CHARGE
User Set New Child Class Charge: "${charge}"
	${eng}=		Robot Gets English Language from separated list		${charge}
	Wait Until Angular Ready
	${sel}=			Convert To String	${CLASS_ED_CHARGE_ENG_IN_JQ} option:contains("${eng}")
	${stat}=		Run Keyword And Return Status	Wait Until Page Contains Element	${sel}	2s
	Run Keyword If	'${stat}' == 'True'		User Set Child Class Charge: "${charge}"
	...		ELSE	User Add New Child Class Charge: "${charge}"
User Add New Child Class Charge: "${charge}"
	${list}=		Split String		${charge}		;
	${eng}=			Remove From List	${list}		0
	${len}=			Get Length	${list}
	
	Wait Until Angular Ready
	Wait Until Page Contains Element	${CLASS_ED_CHARGE_ENG_ADD_JQ}
	Click Button						${CLASS_ED_CHARGE_ENG_ADD_JQ}
	Wait Until Page Contains Element	${CLASS_ED_CHARGE_INPUT_JQ}
	Wait Until Page Contains Element	${CLASS_ED_CHARGE_SET_JQ}
	Input Text							${CLASS_ED_CHARGE_INPUT_JQ}		${eng}
	Click Button						${CLASS_ED_CHARGE_SET_JQ}
	${sel}=			Convert To String	${CLASS_ED_CHARGE_ENG_IN_JQ} option:contains("${eng}")
	Wait Until Page Contains Element	${sel}
	Run Keyword If		${len} > 0		User Set Translates to Block on Modal Dialog	Charge	${list}
User Set Child Class Charge: "${charge}"
	${list}=		Split String		${charge}		;
	${eng}=			Remove From List	${list}		0
	${len}=			Get Length	${list}
	
	Wait Until Angular Ready
	Wait Until Page Contains Element	${CLASS_ED_CHARGE_ENG_IN_JQ}
	${sel}=			Convert To String	${CLASS_ED_CHARGE_ENG_IN_JQ} option:contains("${eng}")
	Click Element						${sel}
	Run Keyword If		${len} > 0		User Set Translates to Block on Modal Dialog	Charge	${list}
User Set Child Class Charge Icon: "${charge_icon}"
	Robot Helps Select an Option with Jquery	${CLASS_ED_CHARGE_ICON_SEL}		${charge_icon}
	
#CHILD CLASS: HL & OTHERS
User Set Child Class Highlight: "${hl}"
	Wait Until Angular Ready
	Wait Until Page Contains Element	${CLASS_ED_HIGHLIGHT_SEL_JQ}
	Run Keyword If	'${hl}' == 'true'	Select Checkbox			${CLASS_ED_HIGHLIGHT_SEL_JQ}
	Run Keyword If	'${hl}' == 'false'	Unselect Checkbox		${CLASS_ED_HIGHLIGHT_SEL_JQ}
User Set Child Class Listable: "${listable}"
	Wait Until Angular Ready
	Wait Until Page Contains Element			${CLASS_ED_LISTABLE_SEL_JQ}
	Run Keyword If	'${listable}' == 'true'		Select Checkbox			${CLASS_ED_LISTABLE_SEL_JQ}
	Run Keyword If	'${listable}' == 'false'	Unselect Checkbox		${CLASS_ED_LISTABLE_SEL_JQ}
User Set Child Class Priority: "${prio}"
	Wait Until Angular Ready
	Wait Until Page Contains Element	${CLASS_ED_PRIORITY_IN_JQ}
	Input Text							${CLASS_ED_PRIORITY_IN_JQ}	${prio}
Robot Check "${name}" Row Name Column of Child Class Table value is "${name2}"
	${sel}=			Convert To String	jquery=label:contains("Child classifications") tr:contains("${name}") td:eq("0")
	Robot Waits Text Visible in Selector	${name}		${sel}
Robot Check "${name}" Row Value Column of Child Class Table value is "${val}"
	${sel}=			Convert To String	jquery=label:contains("Child classifications") tr:contains("${name}") td:eq("1")
	Robot Waits Text Visible in Selector	${val}		${sel}
Robot Check "${name}" Row Highlight Column of Child Class Table value is "${hl}"
	${sel}=			Convert To String	jquery=label:contains("Child classifications") tr:contains("${name}") td:eq("2")
	Robot Waits Text Visible in Selector	${hl}		${sel}
Robot Check "${name}" Row Listable Column of Child Class Table value is "${listable}"
	${sel}=			Convert To String	jquery=label:contains("Child classifications") tr:contains("${name}") td:eq("3")
	Robot Waits Text Visible in Selector	${listable}		${sel}
Robot Check "${name}" Row Searchable Column of Child Class Table value is "${searchable}"
	${sel}=			Convert To String	jquery=label:contains("Child classifications") tr:contains("${name}") td:eq("4")
	Robot Waits Text Visible in Selector	${searchable}		${sel}
Robot Check "${name}" Row Priority Column of Child Class Table value is "${prio}"
	${sel}=			Convert To String	jquery=label:contains("Child classifications") tr:contains("${name}") td:eq("5")
	Robot Waits Text Visible in Selector	${prio}		${sel}
User Click Child Class Save Button
	Wait Until Angular Ready
	Wait until Page Contains Element	${CLASS_ED_SAVE_BTN_JQ}
	Click Button						${CLASS_ED_SAVE_BTN_JQ}
User Click Child Class Clear Button
	Robot Helps Push The Button			${CLASS_ED_CLEAR_BTN_JQ}
The Child Class Save Button is disabled
	#Robot Wait Attribute Value in Selector	${CLASS_ED_SAVE_BTN_JQ}		ng-reflect-disabled		true
	Run Keyword and Return Status
	...						Robot Wait Standalone Attribute in Selector					${CLASS_ED_SAVE_BTN_JQ}	disabled
The Child Class Save Button is enabled
	#Robot Wait Selector Not Contain Attribute	${CLASS_ED_SAVE_BTN_JQ}		ng-reflect-disabled		false
	Run Keyword and Return Status
	...						Robot Wait Standalone Attribute Disappeared from Selector	${CLASS_ED_SAVE_BTN_JQ}	disabled
	
#CHILD META:-------------------------------------------------------------------------------------------------CHILD META:
#CHILD META: NAME
User Set New Child Meta Name: "${name}"
	${list}=		Split String		${name}		;
	${eng}=			Remove From List	${list}		0
	${len}=			Get Length	${list}
	
	Wait Until Angular Ready
	${sel}=			Convert To String	${META_ED_NAME_ENG_IN_JQ} option:contains("${eng}")
	${stat}=		Run Keyword And Return Status	Wait Until Page Contains Element	${sel}	2s
	Run Keyword If	'${stat}' == 'True'		User Set Child Meta Name: "${name}"
	...		ELSE	User Add New Child Meta Name: "${name}"
User Set Empty Child Meta Name
	Robot Helps Push The Button		${META_ED_NAME_ENG_IN_JQ} option:eq(0)
User Add New Child Meta Name: "${name}"
	${list}=		Split String		${name}		;
	${eng}=			Remove From List	${list}		0
	${len}=			Get Length	${list}
	
	Wait Until Angular Ready
	Wait Until Page Contains Element	${META_ED_NAME_ENG_ADD_JQ}
	Click Button						${META_ED_NAME_ENG_ADD_JQ}
	Wait Until Page Contains Element	${META_ED_NAME_INPUT_JQ}
	Wait Until Page Contains Element	${META_ED_NAME_SET_JQ}
	Input Text							${META_ED_NAME_INPUT_JQ}		${eng}
	Click Button						${META_ED_NAME_SET_JQ}
	${sel}=			Convert To String	${META_ED_NAME_ENG_IN_JQ} option:contains("${eng}")
	Wait Until Page Contains Element	${sel}
	
	Run Keyword If		${len} > 0		User Set Translates to Block on Modal Dialog	Name	${list}
	
User Set Child Meta Name: "${name}"
	${list}=		Split String		${name}		;
	${eng}=			Remove From List	${list}		0
	${len}=			Get Length	${list}
	
	Wait Until Angular Ready
	Wait Until Page Contains Element	${META_ED_NAME_ENG_IN_JQ}
	${sel}=			Convert To String	${META_ED_NAME_ENG_IN_JQ} option:contains("${eng}")
	Click Element						${sel}
	
	Run Keyword If		${len} > 0		User Set Translates to Block on Modal Dialog	Name	${list}

User Set Child Meta Name Icon: "${name_icon}"
	Robot Helps Select an Option with Jquery	${CLASS_ED_NAME_ICON_SEL}	${name_icon}
	
#CHILD META: VALUE
User Set New Child Meta Value: "${value}"
	Wait Until Angular Ready
	Wait Until Page Contains Element	${META_ED_VALUE_INPUT_JQ}
	Input Text							${META_ED_VALUE_INPUT_JQ}	${value}
	
User Press Tab key on Meta Value Input
	Press Key			${META_ED_VALUE_INPUT_JQ}	\\09
	
User Press Backspace key on Meta Value Input
	Robot Helps Pressing Backspace Key	${META_ED_VALUE_INPUT_JQ}
	
#CHILD META: DESCRIPTION
User Set Child Meta Description: "${desc}"
	${list}=		Split String		${desc}		;
	${eng}=			Remove From List	${list}		0
	${len}=			Get Length	${list}
	
	Wait Until Angular Ready
	Wait Until Page Contains Element	${META_ED_DESC_ENG_IN_JQ}
	Input Text							${META_ED_DESC_ENG_IN_JQ}	${eng}
	
	Run Keyword If		${len} > 0		User Set Translates to Block on Modal Dialog Textarea	Description		${list}
User Set Child Meta Description to TinyMCE: "${desc}"
	${list}=		Split String		${desc}		;
	${eng}=			Remove From List	${list}		0
	${len}=			Get Length	${list}
	
	Robot Helps Write to TinyMCE		${CLASS_ED_DESC_ENG_TMCE_JQ}	${eng}
	Run Keyword If		${len} > 0		User Set Translates to Block on Modal Dialog TinyMCE	Description		${list}
	
User Set Child Meta Listable: "${listable}"
	Wait Until Angular Ready
	Wait Until Page Contains Element			${META_ED_LISTABLE_SEL_JQ}
	Run Keyword If	'${listable}' == 'true'		Select Checkbox			${META_ED_LISTABLE_SEL_JQ}
	Run Keyword If	'${listable}' == 'false'	Unselect Checkbox		${META_ED_LISTABLE_SEL_JQ}
User Set Child Meta Priority: "${prio}"
	Wait Until Angular Ready
	Wait Until Page Contains Element	${META_ED_PRIORITY_IN_JQ}
	Input Text							${META_ED_PRIORITY_IN_JQ}	${prio}
Robot Check "${name}" Row Name Column of Child Meta Table value is "${name2}"
	${sel}=			Convert To String	jquery=label:contains("Child metas") tr:contains("${name}") td:eq(0)
	Robot Waits Text Visible in Selector	${name}		${sel}
Robot Check "${name}" Row Value Column of Child Meta Table value is "${value}"
	${sel}=			Convert To String	jquery=label:contains("Child metas") tr:contains("${name}") td:eq(1)
	Robot Waits Text Visible in Selector	${value}		${sel}
Robot Check "${name}" Row Listable Column of Child Meta Table value is "${listable}"
	${sel}=			Convert To String	jquery=label:contains("Child metas") tr:contains("${name}") td:eq(2)
	Robot Waits Text Visible in Selector	${listable}		${sel}
Robot Check "${name}" Row Priority Column of Child Meta Table value is "${prio}"
	${sel}=			Convert To String	jquery=label:contains("Child metas") tr:contains("${name}") td:eq(3)
	Robot Waits Text Visible in Selector	${prio}		${sel}
User Click Child Meta Save Button
	Wait Until Angular Ready
	Wait until Page Contains Element	${META_ED_SAVE_BTN_JQ}
	Click Button						${META_ED_SAVE_BTN_JQ}
User Click Child Meta Clear Button
	Robot Helps Push The Button			${META_ED_CLEAR_BTN_JQ}
The Child Meta Save Button is disabled
	#Robot Wait Attribute Value in Selector		${META_ED_SAVE_BTN_JQ}		ng-reflect-disabled		true
	Run Keyword and Return Status
	...						Robot Wait Standalone Attribute in Selector					${META_ED_SAVE_BTN_JQ}	disabled
The Child Meta Save Button is enabled
	#Robot Wait Selector Not Contain Attribute		${META_ED_SAVE_BTN_JQ}		ng-reflect-disabled		false
	Run Keyword and Return Status
	...						Robot Wait Standalone Attribute Disappeared from Selector	${META_ED_SAVE_BTN_JQ}	disabled
#The Properties page contains expected elements
#	Wait Until Angular Ready
#	The Properties Page Contains "7" Tabs
#	Robot Helps Check Properties Inner Tab		${PROP_TABS_BY_NAME}
The Properties Page Contains "${n}" Tabs
	Wait Until Angular Ready
	Wait Until Page Contains Element	${PROP_TABS}
	${e}=			Get Webelements		${PROP_TABS}
	${length}=		Get Length			${e}
	Should Be True	${length} == ${n}
#The Properties page contains extended edit elements
	#Wait Until Page Contains Element	${PROPERTIES_CH_CLASS_TAB_JQ}
	#Page Should Contain Element			${PROPERTIES_CH_CLASS_ADD_JQ}
	#Page Should Contain Element			${PROPERTIES_CH_META_TAB_JQ}
	#Page Should Contain Element			${PROPERTIES_CH_CLASS_ADD_JQ}
	
#The Properties page does not contains extended edit elements
	#Wait Until Page Does Not Contain Element	${PROPERTIES_CH_CLASS_TAB_JQ}
	#Wait Until Page Does Not Contain Element	${PROPERTIES_CH_CLASS_ADD_JQ}
	#Wait Until Page Does Not Contain Element	${PROPERTIES_CH_META_TAB_JQ}
	#Wait Until Page Does Not Contain Element	${PROPERTIES_CH_CLASS_ADD_JQ}

The Classification editor dialog contains expected elements
	Wait Until Page Contains Element		${CLASS_ED_NAME_TABLE_JQ}
	Page Should Contain Element				${CLASS_ED_NAME_ENG_IN_JQ}
	Page Should Contain Element				${CLASS_ED_NAME_ENG_ADD_JQ}
	Page Should Contain Element				${CLASS_ED_VALUE_TABLE_JQ}
	Page Should Contain Element				${CLASS_ED_VALUE_ENG_IN_JQ}
	Page Should Contain Element				${CLASS_ED_VALUE_ENG_ADD_JQ}
	Page Should Contain Element				${CLASS_ED_CHARGE_TABLE_JQ}
	Page Should Contain Element				${CLASS_ED_CHARGE_ENG_IN_JQ}
	Page Should Contain Element				${CLASS_ED_CHARGE_ENG_ADD_JQ}
	Page Should Contain Element				${CLASS_ED_DESC_TABLE_JQ}
	Page Should Contain Element				${CLASS_ED_DESC_ENG_IN_JQ}
	Page Should Contain Element				${CLASS_ED_HIGHLIGHT_SEL_JQ}
	Page Should Contain Element				${CLASS_ED_LISTABLE_SEL_JQ}
	Page Should Contain Element				${CLASS_ED_PRIORITY_IN_JQ}
	Page Should Contain Element				${CLASS_ED_SAVE_BTN_JQ}
	Page Should Contain Element				${CLASS_ED_CLEAR_BTN_JQ}

The Meta editor dialog contains expected elements
	Wait Until Page Contains Element		${META_ED_NAME_TABLE_JQ}
	Page Should Contain Element				${META_ED_NAME_ENG_IN_JQ}
	Page Should Contain Element				${META_ED_NAME_ENG_ADD_JQ}
	Page Should Contain Element				${META_ED_VALUE_INPUT_JQ}
	Page Should Contain Element				${META_ED_DESC_TABLE_JQ}
	Page Should Contain Element				${META_ED_DESC_ENG_IN_JQ}
	Page Should Contain Element				${META_ED_LISTABLE_SEL_JQ}
	Page Should Contain Element				${META_ED_PRIORITY_IN_JQ}
	Page Should Contain Element				${META_ED_SAVE_BTN_JQ}
	Page Should Contain Element				${META_ED_CLEAR_BTN_JQ}

#User Click "${row}" row "${button}" Button Then Check Automatically
#	User Click "${row}" row "${button}" Button
#	The Properties page contains expected elements
#	The Properties page contains extended edit elements
#	The "${row}" is the selected option in Name Select
#	The "${row}" row Highlighted Value is in the Highlighted Checkbox
#	The "${row}" row Listable Value is in the Listable Checkbox
#	The "${row}" row Priority Value is in the Priority Input

User Click "${row}" row "${button}" Button Then Check Automatically on Classification editor
	User Click "${row}" row "${button}" Button
	Classification editor dialog will be visible
	The Classification editor dialog contains expected elements
	The "${row}" is the selected option in Name Select on Classification editor
	The "${row}" row Value Value is in the Value Input on Classification editor
	The "${row}" row Highlighted Value is in the Highlighted Checkbox on Classification editor
	The "${row}" row Listable Value is in the Listable Checkbox on Classification editor
	The "${row}" row Priority Value is in the Priority Input on Classification editor
	User Click Modal Dialog Cancel Button
	
User Click "${row}" row "${button}" Button Then Check Automatically on Meta editor
	User Click "${row}" row "${button}" Button
	Meta editor dialog will be visible
	The Meta editor dialog contains expected elements
	The "${row}" is the selected option in Name Select on Meta editor
	The "${row}" row Value Value is in the Value Input on Meta editor
	The "${row}" row Listable Value is in the Listable Checkbox on Meta editor
	The "${row}" row Priority Value is in the Priority Input on Meta editor
	User Click Modal Dialog Cancel Button

Classification editor dialog will be visible
	Wait Until Page Contains Element			${CLASS_ED_DIV}
Meta editor dialog will be visible
	Wait Until Page Contains Element			${META_ED_DIV}
	
The Page Does Not Contain Classification editor
	Wait Until Element Contains Attribute		${CLASS_ED_DIV}@style	display: none
The Page Does Not Contain Meta editor
	Wait Until Element Contains Attribute		${META_ED_DIV}@style	display: none
	
The "${row}" row Highlighted Value is in the Highlighted Checkbox
	${value_loc}=	Convert To String	jquery=tr:contains("${row}"):eq(1) td:eq(1)
	${value}=		Get Text			${value_loc}
	
	Run Keyword If		'${value}' == 'false'	Checkbox Should Not Be Selected		${PROPERTIES_HIGHLIGHT_SEL_JQ}
	...		ELSE IF		'${value}' == 'true'	Checkbox Should Be Selected			${PROPERTIES_HIGHLIGHT_SEL_JQ}
	
The "${row}" row Listable Value is in the Listable Checkbox
	${value_loc}=	Convert To String	jquery=tr:contains("${row}"):eq(1) td:eq(2)
	${value}=		Get Text			${value_loc}
	
	Run Keyword If		'${value}' == 'false'	Checkbox Should Not Be Selected		${PROPERTIES_LISTABLE_SEL_JQ}
	...		ELSE IF		'${value}' == 'true'	Checkbox Should Be Selected			${PROPERTIES_LISTABLE_SEL_JQ}
The "${row}" row Priority Value is in the Priority Input
	${value_loc}=	Convert To String	jquery=tr:contains("${row}"):eq(1) td:eq(3)
	${value}=		Get Text			${value_loc}
	${pr_in_val}=	Get Element Attribute	${PROPERTIES_PRIORITY_IN_JQ}@ng-reflect-model
	
	Run Keyword If	'${value}' == ''	Should Be Equal As Strings		${pr_in_val}	None
	...		ELSE						Should Be Equal As Strings		${value}	${pr_in_val}
	
The "${name}" is the selected option in Name Select
	${selected}=		Get Selected List Value			${PROPERTIES_NAME_ENG_IN_JQ}
	Should Be Equal As Strings		${name}		${selected}

#Meta editor
The "${row}" row Value Value is in the Value Input on Meta editor
	${value_loc}=	Convert To String	jquery=label:contains("Child metas") tr:contains("${row}"):eq(0) td:eq(1)
	${value}=		Get Text			${value_loc}
	${pr_in_val}=	Get Element Attribute	${META_ED_VALUE_INPUT_JQ}@ng-reflect-model
	
	Run Keyword If	'${value}' == ''	Should Be Equal As Strings		${pr_in_val}	None
	...		ELSE						Should Be Equal As Strings		${value}	${pr_in_val}
	
The "${row}" row Listable Value is in the Listable Checkbox on Meta editor
	${value_loc}=	Convert To String	jquery=label:contains("Child metas") tr:contains("${row}"):eq(0) td:eq(2)
	${value}=		Get Text			${value_loc}
	
	Run Keyword If		'${value}' == 'false'	Checkbox Should Not Be Selected		${META_ED_LISTABLE_SEL_JQ}
	...		ELSE IF		'${value}' == 'true'	Checkbox Should Be Selected			${META_ED_LISTABLE_SEL_JQ}
The "${row}" row Priority Value is in the Priority Input on Meta editor
	${value_loc}=	Convert To String	jquery=label:contains("Child metas") tr:contains("${row}"):eq(0) td:eq(3)
	${value}=		Get Text			${value_loc}
	${pr_in_val}=	Get Element Attribute	${META_ED_PRIORITY_IN_JQ}@ng-reflect-model
	
	Run Keyword If	'${value}' == ''	Should Be Equal As Strings		${pr_in_val}	None
	...		ELSE						Should Be Equal As Strings		${value}	${pr_in_val}

The "${name}" is the selected option in Name Select on Meta editor
	${selected}=		Get Selected List Value			${META_ED_NAME_ENG_IN_JQ}
	Should Be Equal As Strings		${name}		${selected}

#Classification editor
The "${name}" is the selected option in Name Select on Classification editor
	${selected}=		Get Selected List Value			${CLASS_ED_NAME_ENG_IN_JQ}
	Should Be Equal As Strings		${name}		${selected}
	
The "${row}" row Value Value is in the Value Input on Classification editor
	${value_loc}=	Convert To String	jquery=label:contains("Child classifications") tr:contains("${row}"):eq(0) td:eq(1)
	${value}=		Get Text			${value_loc}
	Run Keyword If	'${value}' == ''	Run Keyword And Expect Error	*No options are selected*	Get Selected List Value		${CLASS_ED_VALUE_ENG_IN_JQ}
	...		ELSE						Compare If Not Empty	${CLASS_ED_VALUE_ENG_IN_JQ}		${value}
	
	
The "${row}" row Highlighted Value is in the Highlighted Checkbox on Classification editor
	${value_loc}=	Convert To String	jquery=label:contains("Child classifications") tr:contains("${row}"):eq(0) td:eq(2)
	${value}=		Get Text			${value_loc}
	
	Run Keyword If		'${value}' == 'false'	Checkbox Should Not Be Selected		${CLASS_ED_HIGHLIGHT_SEL_JQ}
	...		ELSE IF		'${value}' == 'true'	Checkbox Should Be Selected			${CLASS_ED_HIGHLIGHT_SEL_JQ}

The "${row}" row Listable Value is in the Listable Checkbox on Classification editor
	${value_loc}=	Convert To String	jquery=label:contains("Child classifications") tr:contains("${row}"):eq(0) td:eq(3)
	${value}=		Get Text			${value_loc}
	
	Run Keyword If		'${value}' == 'false'	Checkbox Should Not Be Selected		${CLASS_ED_LISTABLE_SEL_JQ}
	...		ELSE IF		'${value}' == 'true'	Checkbox Should Be Selected			${CLASS_ED_LISTABLE_SEL_JQ}

The "${row}" row Priority Value is in the Priority Input on Classification editor
	${value_loc}=	Convert To String	jquery=label:contains("Child classifications") tr:contains("${row}"):eq(0) td:eq(4)
	${value}=		Get Text			${value_loc}
	${pr_in_val}=	Get Element Attribute	${CLASS_ED_PRIORITY_IN_JQ}@ng-reflect-model
	
	Run Keyword If	'${value}' == ''	Should Be Equal As Strings		${pr_in_val}	None
	...		ELSE						Should Be Equal As Strings		${value}	${pr_in_val}

#AGE RANGES:-------------------------------------------------------------------------------------------------AGE RANGES:
The Age Ranges Page contains the Expected Elements
	Wait Until Angular Ready
	Wait Until Page Contains Element	${AGE_RANGES_NAME_IN_JQ}	
	Page Should Contain Element			${AGE_RANGES_FROM_IN_JQ}
	Page Should Contain Element			${AGE_RANGES_TO_IN_JQ}
	Page Should Contain Element			${AGE_RANGES_SAVE_BTN_JQ}
	Page Should Contain Element			${AGE_RANGES_CLEAR_BTN_JQ}

User Set Age Range Name: "${name}"
	Wait Until Angular Ready
	Wait Until Page Contains Element	${AGE_RANGES_NAME_IN_JQ}
	#Input Text							${AGE_RANGES_NAME_IN_JQ}	${name}
	#Press Key							${AGE_RANGES_NAME_IN_JQ}	${name}
	Robot Helps Select an Option with Jquery	${AGE_RANGES_NAME_IN_JQ}	${name}
User Select Empty Age Range Name
	Wait Until Angular Ready
	Wait Until Page Contains Element	${AGE_RANGES_NAME_IN_JQ}
	Robot Helps Select an Option by EQ with Jquery		${AGE_RANGES_NAME_IN_JQ}	${0}
User Set Age Range From: "${from}"
	Wait Until Angular Ready
	Wait Until Page Contains Element	${AGE_RANGES_FROM_IN_JQ}
	Input Text							${AGE_RANGES_FROM_IN_JQ}	${from}
User Set Age Range To: "${to}"
	Wait Until Angular Ready
	Wait Until Page Contains Element	${AGE_RANGES_TO_IN_JQ}
	Input Text							${AGE_RANGES_TO_IN_JQ}		${to}
User Set Age Range Banned: "${banned}"
	Run Keyword If		'${banned}' == 'true'	Robot Helps Select Checkbox		${AGE_RANGES_BANNED_IN_JQ}
	...		ELSE IF		'${banned}' == 'false'	Robot Helps Unselect Checkbox	${AGE_RANGES_BANNED_IN_JQ}
User Set Age Range Free: "${free}"
	Run Keyword If		'${free}' == 'true'		Robot Helps Select Checkbox		${AGE_RANGES_FREE_IN_JQ}
	...		ELSE IF		'${free}' == 'false'	Robot Helps Unselect Checkbox	${AGE_RANGES_FREE_IN_JQ}
Robot Check "${name}" Row From value is "${from}"
	Wait Until Angular Ready
	${sel}=			Convert To String	jquery=tr:contains("${name}"):eq(1) td:eq(2)
	Robot Waits Text Visible in Selector	${from}		${sel}
Robot Check "${name}" Row To value is "${to}"
	Wait Until Angular Ready
	${sel}=			Convert To String	jquery=tr:contains("${name}"):eq(1) td:eq(3)
	Robot Waits Text Visible in Selector	${to}		${sel}
Robot Check "${name}" Row Banned value is "${banned}"
	Wait Until Angular Ready
	${sel}=			Convert To String	jquery=tr:contains("${name}"):eq(1) td:eq(4)
	Robot Waits Text Visible in Selector	${banned}		${sel}
Robot Check "${name}" Row Free value is "${free}"
	Wait Until Angular Ready
	${sel}=			Convert To String	jquery=tr:contains("${name}"):eq(1) td:eq(5)
	Robot Waits Text Visible in Selector	${free}		${sel}
User Click Age Range Save Button
	Wait Until Angular Ready
	Wait Until Page Contains Element		${AGE_RANGES_SAVE_BTN_JQ}
	Click Button							${AGE_RANGES_SAVE_BTN_JQ}
User Click Age Range Clear Button
	Wait Until Angular Ready
	Wait Until Page Contains Element		${AGE_RANGES_CLEAR_BTN_JQ}
	Click Button							${AGE_RANGES_CLEAR_BTN_JQ}
The Age Range Save Button is disabled
	#Robot Wait Attribute Value in Selector		${AGE_RANGES_SAVE_BTN_JQ}	ng-reflect-disabled		true
	Run Keyword and Return Status
	...								Robot Wait Standalone Attribute in Selector		${AGE_RANGES_SAVE_BTN_JQ}	disabled
The Age Range Save Button is enabled
	#Robot Wait Selector Not Contain Attribute		${AGE_RANGES_SAVE_BTN_JQ}	ng-reflect-disabled		false
	Run Keyword and Return Status
	...				Robot Wait Standalone Attribute Disappeared from Selector		${AGE_RANGES_SAVE_BTN_JQ}	disabled
User Press Tab key on Age Range Name Input
	Press Key			${AGE_RANGES_NAME_IN_JQ}	\\09
User Press Backspace key on Age Range Name Input
	Robot Helps Pressing Backspace Key		${AGE_RANGES_NAME_IN_JQ}
User Press Tab key on Age Range From Age Input
	Press Key			${AGE_RANGES_FROM_IN_JQ}	\\09
User Press Backspace key on Age Range From Age Input
	Robot Helps Pressing Backspace Key		${AGE_RANGES_FROM_IN_JQ}
User Press Tab key on Age Range To Age Input
	Press Key			${AGE_RANGES_TO_IN_JQ}	\\09
User Press Backspace key on Age Range To Age Input
	Robot Helps Pressing Backspace Key		${AGE_RANGES_TO_IN_JQ}

#PERIODS:-------------------------------------------------------------------------------------------------------PERIODS:
The Periods Page contains the Expected Elements
	Wait Until Angular Ready
	Wait Until Page Contains Element	${PERIODS_S_CLOS_FROM_INPUT}
	Page Should Contain Element		${PERIODS_S_CLOS_TO_INPUT}
	Page Should Contain Element		${PERIODS_S_CLOS_NAME_INPUT}
	Page Should Contain Element		${PERIODS_S_CLOS_SAVE_BTN}
	
	Page Should Contain Element		${PERIODS_S_OP_FROM_INPUT}
	Page Should Contain Element		${PERIODS_S_OP_TO_INPUT}
	Page Should Contain Element		${PERIODS_S_OP_NAME_INPUT}
	Page Should Contain Element		${PERIODS_S_OP_MN_INPUT}
	Page Should Contain Element		${PERIODS_S_OP_SAVE_BTN}
	Page Should Contain Element		${PERIODS_S_OP_MP_EP_CHK}
	Page Should Contain Element		${PERIODS_S_OP_MP_BB_CHK}
	Page Should Contain Element		${PERIODS_S_OP_MP_HB_CHK}
	Page Should Contain Element		${PERIODS_S_OP_MP_FB_CHK}
	Page Should Contain Element		${PERIODS_S_OP_MP_IN_CHK}
	
	Page Should Contain Element		${PERIODS_S_DISC_FROM_INPUT}
	Page Should Contain Element		${PERIODS_S_DISC_TO_INPUT}
	Page Should Contain Element		${PERIODS_S_DISC_NAME_INPUT}
	Page Should Contain Element		${PERIODS_S_DISC_SAVE_BTN}

User Set Closure Period from: "${from}"
	Press Key			${PERIODS_S_CLOS_FROM_INPUT}		${from}
User Set Closure Period to: "${to}"
	Press Key			${PERIODS_S_CLOS_TO_INPUT}			${to}
User Set Closure Name to: "${name}"
	Input Text			${PERIODS_S_CLOS_NAME_INPUT}		${name}
User Press Backspace key on Closure Name Input
	Robot Helps Pressing Backspace Key				${PERIODS_S_CLOS_NAME_INPUT}
The Closure Save Button is disabled
	#Robot Wait Attribute Value in Selector			${PERIODS_S_CLOS_SAVE_BTN}	ng-reflect-disabled		true
	Run Keyword and Return Status
	...					Robot Wait Standalone Attribute in Selector					${PERIODS_S_CLOS_SAVE_BTN}	disabled
The Closure Save Button is enabled
	#Robot Wait Selector Not Contain Attribute		${PERIODS_S_CLOS_SAVE_BTN}	ng-reflect-disabled		false
	Run Keyword and Return Status
	...					Robot Wait Standalone Attribute Disappeared from Selector	${PERIODS_S_CLOS_SAVE_BTN}	disabled

User Set Open Period from: "${from}"
	Press Key			${PERIODS_S_OP_FROM_INPUT}		${from}
User Set Open Period to: "${to}"
	Press Key			${PERIODS_S_OP_TO_INPUT}		${to}
User Set Open Period Name to: "${name}"
	Input Text			${PERIODS_S_OP_NAME_INPUT}		${name}
User Set Open Period Minimum Night to: "${min}"
	Input Text			${PERIODS_S_OP_MN_INPUT}		${min}
User Press Backspace key on Open Period Name Input
	Robot Helps Pressing Backspace Key				${PERIODS_S_OP_NAME_INPUT}
User Press Backspace key on Open Period Minimum Night Input
	Robot Helps Pressing Backspace Key				${PERIODS_S_OP_MN_INPUT}
The Open Period Save Button is disabled
	#Robot Wait Attribute Value in Selector			${PERIODS_S_OP_SAVE_BTN}	ng-reflect-disabled		true
	Run Keyword and Return Status					
	...					Robot Wait Standalone Attribute in Selector					${PERIODS_S_OP_SAVE_BTN}	disabled
The Open Period Save Button is enabled
	#Robot Wait Selector Not Contain Attribute		${PERIODS_S_OP_SAVE_BTN}	ng-reflect-disabled		false
	Run Keyword and Return Status					
	...					Robot Wait Standalone Attribute Disappeared from Selector	${PERIODS_S_OP_SAVE_BTN}	disabled

User Set Discount Period from: "${from}"
	Press Key			${PERIODS_S_DISC_FROM_INPUT}		${from}
User Set Discount Period to: "${to}"
	Press Key			${PERIODS_S_DISC_TO_INPUT}		${to}
User Set Discount Period Name to: "${name}"
	Input Text			${PERIODS_S_DISC_NAME_INPUT}		${name}
User Press Backspace key on Discount Period Name Input
	Robot Helps Pressing Backspace Key				${PERIODS_S_DISC_NAME_INPUT}
The Discount Period Save Button is disabled
	#Robot Wait Attribute Value in Selector			${PERIODS_S_DISC_SAVE_BTN}	ng-reflect-disabled		true
	Run Keyword and Return Status					
	...					Robot Wait Standalone Attribute in Selector					${PERIODS_S_DISC_SAVE_BTN}	disabled
The Discount Period Save Button is enabled
	#Robot Wait Selector Not Contain Attribute		${PERIODS_S_DISC_SAVE_BTN}	ng-reflect-disabled		false
	Run Keyword and Return Status
	...					Robot Wait Standalone Attribute Disappeared from Selector	${PERIODS_S_DISC_SAVE_BTN}	disabled
	
#ROOMS:-----------------------------------------------------------------------------------------------------------ROOMS:
The Rooms Page contains the Expected Elements
	Wait Until Angular Ready
	Wait Until Page Contains Element	${ROOMS_NAME_ENG_JQ}
	Page Should Contain Element			${ROOMS_AMOUNT_JQ}
	Page Should Contain Element			${ROOMS_ADD_USAGE_JQ}
	Page Should Contain Element			${ROOMS_SAVE_BTN_JQ}

The Usage Editor Dialog contains the Expected Elements
	Wait Until Angular Ready
	Wait Until Page Contains Element	${ROOMS_USAGE_ED_AGE_JQ}
	Page Should Contain Element			${ROOMS_USAGE_ED_NUMBER_JQ}
	Page Should Contain Element			${ROOMS_USAGE_ED_SET_USAGE_BTN_JQ}
	Page Should Contain Element			${ROOMS_USAGE_ED_CLEAR_USAGE_BTN_JQ}
	Page Should Contain Element			${ROOMS_USAGE_ED_SAVE_BTN_JQ}
	Page Should Contain Element			${ROOMS_USAGE_ED_CLEAR_BTN_JQ}
	Click Element 						${ROOMS_USAGE_ED_CLEAR_BTN_JQ}
User Set Room Name: "${name}"
	${list}=		Split String		${name}		;
	${eng}=			Remove From List	${list}		0
	${len}=			Get Length	${list}
	
	Wait Until Angular Ready
	Wait Until Page Contains Element	${ROOMS_NAME_ENG_JQ}
	Input Text							${ROOMS_NAME_ENG_JQ}	${eng}
	
	Run Keyword If		${len} > 0		User Set Translates to Block	Name		${list}
User Set New Room Name: "${name}"
	${list}=		Split String		${name}		;
	${eng}=			Remove From List	${list}		0
	#${len}=			Get Length	${list}
	Robot Helps Write to Input with Jquery	${ROOMS_NAME_JQ}	${eng}
User Set Room Amount: "${amount}"
	Wait Until Angular Ready
	Wait Until Page Contains Element	${ROOMS_AMOUNT_JQ}
	Input Text							${ROOMS_AMOUNT_JQ}	${amount}
Robot Check "${name}" Row Amount value is "${amount}"
	Wait Until Angular Ready
	${sel}=			Convert To String	jquery=tr:contains("${name}") td:eq(2)
	Robot Waits Text Visible in Selector	${amount}		${sel}
User Click Room Save Button
	Wait Until Angular Ready
	Wait Until Page Contains Element		${ROOMS_SAVE_BTN_JQ}
	Click Button							${ROOMS_SAVE_BTN_JQ}
User Click Room Clear Button
	Wait Until Angular Ready
	Wait Until Page Contains Element		${ROOMS_CLEAR_BTN_JQ}
	Click Button							${ROOMS_CLEAR_BTN_JQ}
The Room Save Button is disabled
	#Robot Wait Attribute Value in Selector		${ROOMS_SAVE_BTN_JQ}	ng-reflect-disabled		true
	Run Keyword and Return Status 			
	...						Robot Wait Standalone Attribute in Selector					${ROOMS_SAVE_BTN_JQ}	disabled
The Room Save Button is enabled
	#Robot Wait Selector Not Contain Attribute		${ROOMS_SAVE_BTN_JQ}	ng-reflect-disabled		false
	Run Keyword and Return Status
	...						Robot Wait Standalone Attribute Disappeared from Selector	${ROOMS_SAVE_BTN_JQ}	disabled
User Press Tab key on Room Name Input
	Press Key			${ROOMS_NAME_ENG_JQ}	\\09
User Press Backspace key on Room Name Input
	Robot Helps Pressing Backspace Key		${ROOMS_NAME_ENG_JQ}
User Press Tab key on Room Amount Input
	Press Key			${ROOMS_AMOUNT_JQ}	\\09
User Press Backspace key on Room Amount Input
	Robot Helps Pressing Backspace Key		${ROOMS_AMOUNT_JQ}
	
#ROOMS/USAGE:-----------------------------------------------------------------------------------------------ROOMS/USAGE:
User Set Room Usage Age Group: "${age}"
	#Wait Until Angular Ready
	#Wait Until Page Contains Element		${ROOMS_USAGE_ED_AGE_JQ}
	#${age_sel}=		Convert To String		${ROOMS_USAGE_ED_AGE_JQ} option:contains("${age}")
	#Wait Until Page Contains Element		${age_sel}
	#Click Element		${age_sel}
	Robot Helps Select an Option with Jquery	${ROOMS_USAGE_ED_AGE_JQ}	${age}

User Set Room Usage Age Group by Number: "${eq}"
	Robot Helps Select an Option by EQ with Jquery	${ROOMS_USAGE_ED_AGE_JQ}	${eq}
	
User Set Room Usage Age Number: "${num}"
	Wait Until Angular Ready
	Wait Until Page Contains Element	${ROOMS_USAGE_ED_NUMBER_JQ}
	Input Text							${ROOMS_USAGE_ED_NUMBER_JQ}		${num}
User Press Backpace on Room Usage Age Number
	Robot Helps Pressing Backspace Key		${ROOMS_USAGE_ED_NUMBER_JQ}
Robot Check "${name}" Row Number value is "${number}"
	Wait Until Angular Ready
	${sel}=			Convert To String	jquery=tr:contains("${name}") td:eq(1)
	Robot Waits Text Visible in Selector	${number}		${sel}
User Click Room Usage Save Button
	Wait Until Angular Ready
	Wait Until Page Contains Element		${ROOMS_USAGE_ED_SAVE_BTN_JQ}
	Click Button							${ROOMS_USAGE_ED_SAVE_BTN_JQ}
User Click Room Usage Clear Button
	Wait Until Angular Ready
	Wait Until Page Contains Element		${ROOMS_USAGE_ED_CLEAR_BTN_JQ}
	Click Button							${ROOMS_USAGE_ED_CLEAR_BTN_JQ}
User Click Room Usage Set Usage Button
	Wait Until Angular Ready
	Wait Until Page Contains Element		${ROOMS_USAGE_ED_SET_USAGE_BTN_JQ}
	Click Button							${ROOMS_USAGE_ED_SET_USAGE_BTN_JQ}
User Click Room Usage Clear Usage Button
	Wait Until Angular Ready
	Wait Until Page Contains Element		${ROOMS_USAGE_ED_CLEAR_USAGE_BTN_JQ}
	Click Button							${ROOMS_USAGE_ED_CLEAR_USAGE_BTN_JQ}
The Room Set Usage Button is disabled
	#Robot Wait Attribute Value in Selector		${ROOMS_USAGE_ED_SET_USAGE_BTN_JQ}	ng-reflect-disabled		true
	Run Keyword and Return Status			
	...			Robot Wait Standalone Attribute in Selector					${ROOMS_USAGE_ED_SET_USAGE_BTN_JQ}	disabled
The Room Set Usage Button is enabled
	#Robot Wait Selector Not Contain Attribute		${ROOMS_USAGE_ED_SET_USAGE_BTN_JQ}	ng-reflect-disabled		false
	Run Keyword and Return Status
	...			Robot Wait Standalone Attribute Disappeared from Selector	${ROOMS_USAGE_ED_SET_USAGE_BTN_JQ}	disabled

#ROOM MIN. NIGHTS
User Select Multiple Cells in Room "${room_name}" by Date "${dates}"
	${date_list}= 				Split String	${dates}	;
	Wait Until Page Contains Element		${ROOM_MIN_N_DATES}
	${dates_elements}=			Get Webelements		${ROOM_MIN_N_DATES}
	${dates_on_site_list}=		Create List
	
	:FOR	${e} 	IN	@{dates_elements}
	\	${tx}=	Get Text	${e}
	\	Append To List		${dates_on_site_list}	${tx}
	
	:FOR	${date} 	IN	@{date_list}
	\	${eq}=	Get Index From List		${dates_on_site_list}	${date}
	\	${sel}=		Convert To String		jquery=tr:has(th:contains("${room_name}")) td:eq(${eq})
	\	Click Element	${sel}

User Set Common Value: "${value}"
	Robot Helps Write to Input with Jquery	${ROOM_MIN_N_COMMON_VALUE_INPUT}	${value}
	Robot Helps Push The Button				${ROOM_MIN_N_SET_BTN}
	
User Set Minimum Nights One by One: Room Name: "${room_name}" Dates: "${dates}" Values: "${value}"
	${date_list}= 				Split String	${dates}	;
	${value_list}= 				Split String	${value}	;
	Wait Until Page Contains Element		${ROOM_MIN_N_DATES}
	${dates_elements}=			Get Webelements		${ROOM_MIN_N_DATES}
	${dates_on_site_list}=		Create List
	#${dates_and_values}=		Create Dictionary
	
	:FOR	${e} 	IN	@{dates_elements}
	\	${tx}=	Get Text	${e}
	\	Append To List		${dates_on_site_list}	${tx}
	
	${length}= 	Get Length	${date_list}
	:FOR	${index} 	IN RANGE	${length}
	#\	Set To Dictionary	${dates_and_values}		@{date_list}[${index}]	@{value_list}[${index}]
	\	${date}= 	Get From List			${date_list}			${index}
	\	${eq}=		Get Index From List		${dates_on_site_list}	${date}
	\	${sel}=		Convert To String		jquery=tr:has(th:contains("${room_name}")) td:eq(${eq}) input
	\	${value}= 	Get From List			${value_list}			${index}
	\	Robot Helps Write to Input with Jquery	${sel}	${value}

#AVAILABILITIES:
The Availabilities Page contains the Expected Elements
	Wait Until Angular Ready
	Wait Until Page Contains Element		${AVLBLTS_S_ROOM_SELECT}
	
The Selected Availabilities Page contains the Expected Elements
	Wait Until Angular Ready
	Page Should Contain Element		${AVLBLTS_S_ROOM_SELECT}
	#Page Should Contain Element		${AVLBLTS_S_ROOM_SELECT}
	#Page Should Contain Element		${AVLBLTS_S_AVAILABLE_BTN}
	#Page Should Contain Element		${AVLBLTS_S_NOT_AVAILABLE_BTN}
	#Page Should Contain Element		${AVLBLTS_S_CLEAR_SEL_BTN}
	#Page Should Contain Element		${AVLBLTS_S_YEAR_SEL}
	#Page Should Contain Element		${AVLBLTS_S_AVLBLT_TABLE}

#PRICES:
The Prices Page contains the Expected Elements
	Wait Until Angular Ready
	Wait Until Page Contains Element	${PRICES_S_NET_PRICES_CHK}
	Page Should Contain Element		${PRICES_S_MARGINS_CHK}
	Page Should Contain Element		${PRICES_S_RACK_PRICES_CHK}
	Page Should Contain Element		${PRICES_S_EDIT_MODE}
	
The Checkboxes on Prices Page are in default state
	Checkbox Should Be Selected		${PRICES_S_NET_PRICES_CHK} input
	Checkbox Should Be Selected		${PRICES_S_MARGINS_CHK} input
	Checkbox Should Be Selected		${PRICES_S_RACK_PRICES_CHK} input
	#Checkbox Should Not Be Selected		${PRICES_S_EDIT_MODE_CHK} input
	
User Click Add New Price Row Button to "${room_name}" Room
	${add_new_select}=	Convert To String	jquery=tbody:contains("${room_name}") tr button:contains("Add price row")
	Wait Until Page Contains Element	${add_new_select}
	Click Element						${add_new_select}
	
User Click "${button}" Button in "${row}" Named Price Row in "${room_name}" Room
	${btn_glyphicon}	Get From Dictionary		${BUTTONS_WITH_GLYPHICON}	${button}
	#${sel}=				Convert To String	jquery=tbody:contains("${room_name}") th:contains("${row}") button:contains("${button}")
	${sel}=				Convert To String	jquery=tbody:contains("${room_name}") th:contains("${row}") button:has(${btn_glyphicon})
	Wait Until Angular Ready
	Wait Until Page Contains Element	${sel}
	Click Element	${sel}
	
The Prices Page contains the Price row editor modal
	Robot Wait Attribute Value in Selector	${PRICES_ADD_PRICE_MODAL_JQ}	style	display: block;
	#Wait Until Angular Ready
	#Wait Until Page Contains Element	${PRICES_ADD_NAME_ENG_JQ}
#The Prices Page does not contains the Price row editor modal
#	Robot Wait Attribute Value in Selector	${PRICES_ADD_PRICE_MODAL_JQ}	style	display: none;

User Set New Price Row Name: "${row_name}"
	${list}=		Split String		${row_name}		;
	${eng}=			Remove From List	${list}		0
	${len}=			Get Length	${list}
	
#	Wait Until Angular Ready
#	Wait Until Page Contains Element	${PRICES_ADD_NAME_ENG_JQ}
#	Input Text							${PRICES_ADD_NAME_ENG_JQ}	${eng}
	
	Robot Helps Select an Option with Jquery	${PRICES_ADD_NAME_ENG_JQ}	${eng}
	
	Run Keyword If		${len} > 0		User Set Translates to Block on Modal Dialog	Name		${list}
	
User Set New Price Row Age Range: "${age}"
	Wait Until Angular Ready
	Wait Until Page Contains Element	${PRICES_ADD_AGE_RANGE_JQ}
	${sel}=			Convert To String	${PRICES_ADD_AGE_RANGE_JQ} option:contains("${age}")
	Click Element						${sel}
User Set New Price Row Amount: "${amount}"
	Wait Until Angular Ready
	Wait Until Page Contains Element	${PRICES_ADD_AMOUNT_JQ}
	Input text							${PRICES_ADD_AMOUNT_JQ}		${amount}
User Set New Price Row Extra: "${extra}"
	Wait Until Angular Ready
	Wait Until Page Contains Element	${PRICES_ADD_EXTRA_JQ}
	Run Keyword If		'${extra}' == 'true'	Select Checkbox			${PRICES_ADD_EXTRA_JQ}
	...		ELSE IF		'${extra}' == 'false'	Unselect Checkbox		${PRICES_ADD_EXTRA_JQ}
User Click Save New Price Button
	Wait Until Angular Ready
	Wait Until Page Contains Element	${PRICES_ADD_SAVE_JQ}
	Click Element						${PRICES_ADD_SAVE_JQ}
Robot Check "${eng}" Price Row is exists in "${room_name}" Named Room
	${sel}=		Convert To String		jquery=tbody:contains("${room_name}") th:contains("${eng}")
	Wait Until Angular Ready
	Wait Until Page Contains Element	${sel}
Robot Can't find "${eng}" Named row in "${room_name}" Named Room
	${sel}=		Convert To String		jquery=tbody:contains("${room_name}") th:contains("${eng}")
	Wait Until Angular Ready
	Wait Until Page Does Not Contain Element	${sel}

#DISCOUNTS:
The Discounts Page contains the Expected Elements
	Wait Until Angular Ready
	:FOR	${ELEMENT}	IN	@{DISCOUNT_PAGE_EXCEPTED}
    \	Wait Until Page Contains Element	${ELEMENT}	2s
	#Page Should Contain Element		${DISCOUNTS_S_NAME_TXT_INPUT}
	#Page Should Contain Element		${DISCOUNTS_S_NAME_LAN_INPUT}
	#Page Should Contain Element		${DISCOUNTS_S_NAME_SET_BTN}
	#Page Should Contain Element		${DISCOUNTS_S_FROM_INPUT}
	#Page Should Contain Element		${DISCOUNTS_S_TO_INPUT}
	#Page Should Contain Element		${DISCOUNTS_S_TYPE_INPUT}
	#Page Should Contain Element		${DISCOUNTS_S_OTHER_INPUT}
	#Page Should Contain Element		${DISCOUNTS_S_SAVE_BTN}
	

#DISCOUNT COMBINATIONS:
User Check "${columns}" Checkboxes in Discount Combination Table Row "${row_name}"
	Return From Keyword If		'${columns}' == '${EMPTY}'
	@{list}=		Split String		${columns}		;
	
	Robot Helps Uncheck all Possible Checkboxes		${row_name}
	Run Keyword If				'${columns}' == 'CLEAR'		User Click Discount Combination Save Button
	Return From Keyword If		'${columns}' == 'CLEAR'
	:FOR	${ELEMENT}	IN	@{list}
    \	User Select "${ELEMENT}" Checkbox in "${row_name}"
	
	User Click Discount Combination Save Button
	#${alert}=	Run Keyword and Return Status
	#...			Robot Should Get a Waited Modal Alert with This Message: "${ALERT_NO_QUERY_TAX}" and Press "OK"
	#Run Keyword If	'${alert}' == 'True'	Log		'${ALERT_NO_QUERY_TAX}' Alert in page as known error	WARN
	
User Select "${chk}" Checkbox in "${row_name}"
	${id_row}=		Robot Get Element Id Number		jquery=tbody th:contains("${row_name}"):eq(0)	2
	${id_col}=		Robot Get Element Id Number		jquery=thead th:contains("${chk}"):eq(0)		2
	${id_sel}=		Convert To String	id=price-modifier_combination_${id_row}_${id_col}
	#${id_sel}=		Convert To String	id=price-modifier_combination_${id_col}_${id_row}
	Wait Until Page Contains Element	${id_sel}
	Select Checkbox				${id_sel}
	
User Click Discount Combination Save Button
	Wait Until Angular Ready
	Wait Until Page Contains Element	${ORG_DISC_COMBO_SAVE}
	Click Element						${ORG_DISC_COMBO_SAVE}
	
#GALLERY:
User Open Gallery Upload Modal
	Robot Helps Push The Button		${GALLERY_UPLOAD_BTN}
User Open File Chooser
	Robot Helps Push The Button		${GALLERY_FILE_CHOOSER}
User Choose This File: "${path}"
	Log		${path}		WARN
	Choose File		${GALLERY_FILE_CHOOSER}		${path}
User Click Upload All Button
	Robot Helps Push The Button		${GALLERY_UPLOAD_ALL_BTN}
User Click Done Button Gallery Upload Modal
	Robot Helps Push The Button		${GALLERY_UPLOAD_DONE_BTN}
User Click Set Button Gallery Edit Modal
	Robot Helps Push The Button		${GALLERY_EDIT_SET_BTN}
User Set Description To Image: "${description}"
	${list}=		Split String		${description}		;
	${eng}=			Remove From List	${list}		0
	${len}=			Get Length	${list}
	
	Wait Until Angular Ready
	Wait Until Page Contains Element	${GALLERY_ENG_DESC_JQ}
	Input Text							${GALLERY_ENG_DESC_JQ}	${eng}
	
	Run Keyword If		${len} > 0		User Set Translates to Block on Modal Dialog	Description		${list}
User Select Image: "${image}"
	${sel}=		Run Keyword If		'${image}' == 'first' or '${image}' == 'last'
	...								Convert To String	jquery=.gallery-items-table tbody tr:${image} a:contains("Edit")
	...			ELSE				Convert To String	jquery=.gallery-items-table tbody tr:eq(${image}) a:contains("Edit")
	Robot Helps Push The Button		${sel}
	[Return]	jquery=.gallery-items-table tbody tr:${image}
User Delete Image: "${image}"
	${sel}=		Run Keyword If		'${image}' == 'first' or '${image}' == 'last'
	...								Convert To String	jquery=.gallery-items-table tbody tr:${image} a:contains("Delete")
	...			ELSE				Convert To String	jquery=.gallery-items-table tbody tr:eq(${image}) a:contains("Delete")
	Robot Helps Push The Button		${sel}
User Set Image Highlight: "${hl}"
	Wait Until Angular Ready
	Wait Until Page Contains Element	${GALLERY_HIGHLIGHT_SEL_JQ}
	Run Keyword If	'${hl}' == 'true'	Select Checkbox			${GALLERY_HIGHLIGHT_SEL_JQ}
	Run Keyword If	'${hl}' == 'false'	Unselect Checkbox		${GALLERY_HIGHLIGHT_SEL_JQ}
User Set Gallery Name: "${name}"
	${list}=		Split String		${name}		;
	${eng}=			Remove From List	${list}		0
	${len}=			Get Length	${list}
	
	Wait Until Angular Ready
	Wait Until Page Contains Element	${GALLERY_ENG_NAME_JQ}
	Input Text							${GALLERY_ENG_NAME_JQ}	${eng}
	
	Run Keyword If		${len} > 0		User Set Translates to Block	Gallery name		${list}
User Set Gallery Role: "${role}"
	Robot Helps Select an Option with Jquery	${GALLERY_ROLE_SEL_JQ}	${role}