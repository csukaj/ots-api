*** Settings ***
Library					ExtendedSelenium2Library
Library					String
Library					Collections
Library					json
Resource				resource_basic_functions.robot
#Resource				resource_helpers.robot
Resource				resource_navigations.robot
Resource				resource_templates.robot
Resource				resource_variables.robot

*** Keywords ***
#EVENTS:
Robot Helps Select an Option with Jquery	
	[Arguments]		${sel}	${op}
	Wait Until Angular Ready
	${selector}=			Convert To String	${sel} option:contains("${op}")
	Wait Until Page Contains Element	${selector}
	Click Element						${selector}
Robot Helps Select an Option by EQ with Jquery
	[Arguments]		${sel}	${eq}
	Wait Until Angular Ready
	${selector}=			Convert To String	${sel} option:eq(${eq})
	Wait Until Page Contains Element	${selector}
	Click Element						${selector}
Robot Helps Write to Input with Jquery	
	[Arguments]		${input}	${text}
	Wait Until Angular Ready
	Wait Until Page Contains Element	${input}
	Input Text							${input}	${text}
Robot Helps Write to TinyMCE
	[Arguments]		${input}	${text}
	Wait Until Angular Ready
	Wait Until Page Contains Element	${input}
	#Input Text							${input}	${text}
	Select Frame						${input}
	Press Key							css=body		\\01
	Press Key							css=body		${text}
	[Teardown]	Unselect Frame
Robot Helps Push The Button
	[Arguments]		${selector}
	Wait Until Angular Ready
	Wait Until Page Contains Element	${selector}
	Click Element						${selector}
Robot Helps Select Checkbox
	[Arguments]		${selector}
	Wait Until Angular Ready
	Wait Until Page Contains Element	${selector}
	Select Checkbox						${selector}
Robot Helps Unselect Checkbox
	[Arguments]		${selector}
	Wait Until Angular Ready
	Wait Until Page Contains Element	${selector}
	Unselect Checkbox					${selector}
	
#CHECK ON PAGE:
Robot Helps Compare Selected Option	
	[Arguments]		${selector}		${value}
	${option}=		Convert To String	${selector} option:contains("${value}")
	Wait Until Angular Ready
	Wait Until Page Contains Element	${option}
	${option_id}=		Get Element Attribute	${option}@ng-reflect-ng-value
	${select_id}=		Get Element Attribute	${selector}@ng-reflect-model
	Should Be Equal		${select_id}	${option_id}
	
Robot Helps Compare Input Value
	[Arguments]		${selector}		${value}
	Wait Until Angular Ready
	Wait Until Page Contains Element	${selector}
	${selector_tx}=		Get Element Attribute	${selector}@ng-reflect-model
	Should Be Equal As Strings		${selector_tx}		${value}
	
Robot Check The Row Is Exist
	[Arguments]		${row}
	${sel}=		Convert To String	jquery=tr:contains("${row}")
	${stat}=	Run Keyword And Return Status
	...			Wait Until Page Contains Element	${sel}	2s
	[Return]	${stat}

Robot Waits Text Disappear from Selector
	[Arguments]		${msg}	${sel}
	${wait}=	Get Selenium Implicit Wait
	Set Selenium Implicit Wait	1
	Wait Until Page Contains Element	${sel}
	Run Keyword And Continue On Failure		Element Should Not Contain		${sel}	${msg}
	Log		Text: "${msg}" disappear from: "${sel}"
	Set Selenium Implicit Wait	${wait}
	
Robot Waits Text Visible in Selector
	[Arguments]		${msg}	${sel}
	Wait Until Angular Ready
	Wait Until Page Contains Element	${sel}
	Wait Until Element Contains		${sel}	${msg}
	Log		Element: "${sel}" contains: "${msg}"
	
Robot Wait Attribute Value in Selector
	[Arguments]		${sel}		${attr_key}		${attr_value}

	Wait Until Angular Ready
	Wait Until Page Contains Element	${sel}
	Wait Until Element Contains Attribute	${sel}@${attr_key}	${attr_value}
	
Robot Wait Selector Not Contain Attribute
	[Arguments]		${sel}		${attr_key}		${attr_value}
	Wait Until Angular Ready
	Wait Until Page Contains Element	${sel}
	${run}=		Run Keyword And Return Status		Wait Until Element Does Not Contain Attribute	${sel}@${attr_key}	${attr_value}
	Return From Keyword If	'${run}' == 'True'
	Run Keyword And Expect Error	*NoneType*		Wait Until Element Does Not Contain Attribute	${sel}@${attr_key}	${attr_value}
	
Robot Wait Standalone Attribute in Selector
	[Arguments]		${sel}		${attr_key}
	${msg_pass}			Convert To String	Selector: ${sel} has attribute: ${attr_key}
	${msg_fail}			Convert To String	Selector: ${sel} has not attribute: ${attr_key}
	Wait Until Angular Ready
	Wait Until Page Contains Element	${sel}
	${selector_with_attribute}	Convert To String	${sel}[${attr_key}]
	${elements}		Run Keyword And Ignore Error	
	...					Get Webelements		${selector_with_attribute}
	${length}		Get Length				${elements}
	Run Keyword If	${length} > 0	Pass Execution	${msg_pass}
	...			ELSE				Fail	${msg_fail}
	#Wait Until Element Contains Attribute	${sel}	${attr_key}@${EMPTY}
	
Robot Wait Standalone Attribute Disappeared from Selector
	[Arguments]		${sel}		${attr_key}
	${msg_pass}			Convert To String	Selector: ${sel} has not attribute: ${attr_key}
	${msg_fail}			Convert To String	Selector: ${sel} has attribute: ${attr_key}
	Wait Until Angular Ready
	Wait Until Page Contains Element	${sel}
	${selector_with_attribute}	Convert To String	${sel}[${attr_key}]
	Wait Until Page Does Not Contain Element		${selector_with_attribute}	15s
	#${elements}		Run Keyword And Ignore Error	
	#...					Get Webelements		${selector_with_attribute}
	#${length}		Get Length				${elements}
	#Run Keyword If	${length} == 0	Pass Execution	${msg_pass}
	#...			ELSE				Fail	${msg_fail}
	#${selector_with_attribute}	Convert To String	${sel}[${attr_key}]
	#Wait Until Element Does Not Contain Attribute	${sel}	${attr_key}@${EMPTY}
	
#ERRORS on page:
"${msg}" Error Message will be visible on page
	Robot Waits Text Visible in Selector	${msg}	${BODY_JQ}
"${msg}" Error Message will disappear
	Robot Waits Text Disappear from Selector	${msg}	${BODY_JQ}
	
#ERRORS on forms:
"${msg}" Error Message will be visible in Closures form
	Robot Waits Text Visible in Selector	${msg}	${PERIODS_CLOSURE_FORM_JQ}
"${msg}" Error Message will be visible in Open Periods form
	Robot Waits Text Visible in Selector	${msg}	${PERIODS_OPEN_FORM_JQ}	
"${msg}" Error Message will be visible in "${fieldset}" Closure fieldset
	${sel}=		Convert To String	jquery=fieldset:contains("${fieldset}"):eq(${EQ_CLOSURE_PERIOD})
	Robot Waits Text Visible in Selector	${msg}	${sel}
"${msg}" Error Message will be visible in "${fieldset}" Open Period fieldset
	${sel}=		Convert To String	jquery=fieldset:contains("${fieldset}"):eq(${EQ_OPEN_PERIOD})
	Robot Waits Text Visible in Selector	${msg}	${sel}
	
"${msg}" Error Message will disappear from Closures form
	Robot Waits Text Disappear from Selector	${msg}		${PERIODS_CLOSURE_FORM_JQ}
"${msg}" Error Message will disappear from Open Periods form
	Robot Waits Text Disappear from Selector	${msg}		${PERIODS_OPEN_FORM_JQ}
"${msg}" Error Message will disappear from "${fieldset}" Closure fieldset
	${sel}=		Convert To String	jquery=fieldset:contains("${fieldset}"):eq(${EQ_CLOSURE_PERIOD})
	Robot Waits Text Disappear from Selector	${msg}		${sel}
"${msg}" Error Message will disappear from "${fieldset}" Open Period fieldset
	${sel}=		Convert To String	jquery=fieldset:contains("${fieldset}"):eq(${EQ_OPEN_PERIOD})
	Robot Waits Text Disappear from Selector	${msg}		${sel}

#Discounts:
"${msg}" Error Message will be visible in Discount Periods form
	Robot Waits Text Visible in Selector	${msg}	${PERIODS_DISC_FORM_JQ}
"${msg}" Error Message will be visible in "${fieldset}" Discount Period fieldset
	${sel}=		Convert To String	jquery=fieldset:contains("${fieldset}"):eq(${EQ_DISCOUNT_PERIOD})
	Robot Waits Text Visible in Selector	${msg}	${sel}

"${msg}" Error Message will disappear from Discount Periods form
	Robot Waits Text Disappear from Selector	${msg}		${PERIODS_DISC_FORM_JQ}
"${msg}" Error Message will disappear from "${fieldset}" Discount Period fieldset
	${sel}=		Convert To String	jquery=fieldset:contains("${fieldset}"):eq(${EQ_DISCOUNT_PERIOD})
	Robot Waits Text Disappear from Selector	${msg}		${sel}
	
#COMMON ERRORS IN FIELDSET
"${msg}" Error Message will be visible in "${fieldset}" fieldset
	${sel}=		Convert To String	jquery=fieldset:contains("${fieldset}")
	Robot Waits Text Visible in Selector	${msg}	${sel}
	
"${msg}" Error Message will disappear from "${fieldset}" fieldset
	${sel}=		Convert To String	jquery=fieldset:contains("${fieldset}")
	Robot Waits Text Disappear from Selector	${msg}		${sel}
	
#MODAL ALERT ERROR:
Robot Should Get a Waited Modal Alert with This Message: "${msg}" and Press "${btn}"
	${modal}=		Convert To String	jquery=.modal-body:visible
	Wait Until Keyword Succeeds		4x		500ms
	...				Element Should Contain		${modal}	${msg}
	${btn}=			Convert To String	jquery=.modal-body button:contains("${btn}")
	Click Button	${btn}

#-----------------------------------------------------------------------------------------------------------------------
	
Compare If Not Empty
	[Arguments]		${sel}		${value}
	${selected}=		Get Selected List Value			${sel}
	Should Be Equal As Strings		${value}		${selected}
	
Robot Get Element Id Number
	[Arguments]		${element}	${i}
	Wait Until Page Contains Element	${element}
	${id}=		Get Element Attribute	${element}@id
	@{n}=		Split String	${id}	_
	#[Return]		@{n}[1]
	[Return]		@{n}[${i}]

# OTHER STUFFS:
Robot Helps Pressing Backspace Key
	[Arguments]		${sel}
	${str}=		Get Value	${sel}
	Log				${str}
	${len}=		Get Length	${str}
	Repeat Keyword	${len}	Press Key	${sel}	\\08
Robot Helps Pressing Backspace Key on iframe
	[Arguments]		${sel}
	Select Frame	${sel}
	Click Element	css=body
	Press Key		css=body	\\01
	Press Key		css=body	\\08
	[Teardown]		Unselect Frame
	
Robot Create Loop in Named Rows in Child Classification Table
	${rows}=		Convert To String	jquery=table:eq(2) tbody tr
	${cells}=		Convert To String	td:eq(0)
	
	${list}=		Robot Gets Named Rows		${rows}		${cells}
	@{list_tab}=	Convert To List		${list}
	:FOR	${ELEMENT}	IN	@{list_tab}
    \	User Click "${ELEMENT}" row "Edit" Button Then Check Automatically on Classification editor
	
Robot Create Loop in Named Rows in Child Metas Table
	${rows}=		Convert To String	jquery=table:eq(3) tbody tr
	${cells}=		Convert To String	td:eq(0)
	
	${list}=		Robot Gets Named Rows		${rows}		${cells}
	@{list_tab}=	Convert To List		${list}
	:FOR	${ELEMENT}	IN	@{list_tab}
    \	User Click "${ELEMENT}" row "Edit" Button Then Check Automatically on Meta editor
	
Robot Gets Named Rows
	[Arguments]			${rows}		${cells}
	${list}=			Create List	
	#table:eq(2) tbody tr:eq(1) td:eq(0)
	${stat}=			Run Keyword And Return Status	Wait Until Page Contains Element	${rows}		3s
	Return From Keyword If	'${stat}' == 'False'	${list}
	
	${row_n}=			Get Webelements		${rows}
	${len}=				Get Length	${row_n}
	:FOR	${index}	IN RANGE	${len}
	\	${sel}=		Convert To String		${rows}:eq(${index}) ${cells}
	\	${text}=	Get Text	${sel}
	\	Append To List		${list}		${text}
	
	[Return] 	${list}
	
User Set Translates to Block
	[Arguments]		${label}		${list}

	${len}=				Get Length		${list}
	:FOR	${index}	IN RANGE	${len}
	\		${translate}=	Get From List	${list}		${index}
	\		Run Keyword If		${index} == 0	User Set "German" Translate: "${translate}" to "${label}" block
	\		...		ELSE IF		${index} == 1	User Set "Hungarian" Translate: "${translate}" to "${label}" block
	\		...		ELSE IF		${index} == 2	User Set "Russian" Translate: "${translate}" to "${label}" block

User Set Translates to Block Textarea
	[Arguments]		${label}		${list}

	${len}=				Get Length		${list}
	:FOR	${index}	IN RANGE	${len}
	\		${translate}=	Get From List	${list}		${index}
	\		Run Keyword If		${index} == 0	User Set "German" Translate: "${translate}" to "${label}" block Textarea
	\		...		ELSE IF		${index} == 1	User Set "Hungarian" Translate: "${translate}" to "${label}" block Textarea
	\		...		ELSE IF		${index} == 2	User Set "Russian" Translate: "${translate}" to "${label}" block Textarea
	
User Set Translates to Block TinyMCE
	[Arguments]		${label}		${list}

	${len}=				Get Length		${list}
	:FOR	${index}	IN RANGE	${len}
	\		${translate}=	Get From List	${list}		${index}
	\		Run Keyword If		${index} == 0	User Set "German" Translate: "${translate}" to "${label}" block TinyMCE
	\		...		ELSE IF		${index} == 1	User Set "Hungarian" Translate: "${translate}" to "${label}" block TinyMCE
	\		...		ELSE IF		${index} == 2	User Set "Russian" Translate: "${translate}" to "${label}" block TinyMCE
	
User Set Translates to Block on Modal Dialog
	[Arguments]		${label}		${list}

	${len}=				Get Length		${list}
	:FOR	${index}	IN RANGE	${len}
	\		${translate}=	Get From List	${list}		${index}
	\		Run Keyword If		${index} == 0	User Set "German" Translate: "${translate}" to "${label}" block on Modal Dialog
	\		...		ELSE IF		${index} == 1	User Set "Hungarian" Translate: "${translate}" to "${label}" block on Modal Dialog
	\		...		ELSE IF		${index} == 2	User Set "Russian" Translate: "${translate}" to "${label}" block on Modal Dialog
	
User Set Translates to Block on Modal Dialog Textarea
	[Arguments]		${label}		${list}

	${len}=				Get Length		${list}
	:FOR	${index}	IN RANGE	${len}
	\		${translate}=	Get From List	${list}		${index}
	\		Run Keyword If		${index} == 0	User Set "German" Translate: "${translate}" to "${label}" block on Modal Dialog Textarea
	\		...		ELSE IF		${index} == 1	User Set "Hungarian" Translate: "${translate}" to "${label}" block on Modal Dialog Textarea
	\		...		ELSE IF		${index} == 2	User Set "Russian" Translate: "${translate}" to "${label}" block on Modal Dialog Textarea
	
User Set Translates to Block on Modal Dialog TinyMCE
	[Arguments]		${label}		${list}

	${len}=				Get Length		${list}
	:FOR	${index}	IN RANGE	${len}
	\		${translate}=	Get From List	${list}		${index}
	\		Run Keyword If		${index} == 0	User Set "German" Translate: "${translate}" to "${label}" block on Modal Dialog TinyMCE
	\		...		ELSE IF		${index} == 1	User Set "Hungarian" Translate: "${translate}" to "${label}" block on Modal Dialog TinyMCE
	\		...		ELSE IF		${index} == 2	User Set "Russian" Translate: "${translate}" to "${label}" block on Modal Dialog TinyMCE
	
User Set "${lang}" Translate: "${translate}" to "${label}" block
	Wait Until Angular Ready
	${sel}=		Convert To String	jquery=label:contains("${label}") tr:contains("${lang}") input
	Wait Until Page Contains Element		${sel}
	Input Text		${sel}		${translate}
User Set "${lang}" Translate: "${translate}" to "${label}" block Textarea
	Wait Until Angular Ready
	${sel}=		Convert To String	jquery=label:contains("${label}") tr:contains("${lang}") textarea
	Wait Until Page Contains Element		${sel}
	Input Text		${sel}		${translate}
User Set "${lang}" Translate: "${translate}" to "${label}" block TinyMCE
	Wait Until Angular Ready
	${sel}=		Convert To String	jquery=label:contains("${label}") tr:contains("${lang}") iframe
	Wait Until Page Contains Element		${sel}
	Select Frame						${sel}
	Press Key							css=body		\\01
	Press Key							css=body		${translate}
	[Teardown]	Unselect Frame
	
User Set "${lang}" Translate: "${translate}" to "${label}" block on Modal Dialog
	Wait Until Angular Ready
	${sel}=		Convert To String	jquery=.modal-dialog label:contains("${label}") tr:contains("${lang}") input
	Wait Until Page Contains Element		${sel}
	Input Text		${sel}		${translate}

User Set "${lang}" Translate: "${translate}" to "${label}" block on Modal Dialog Textarea
	Wait Until Angular Ready
	${sel}=		Convert To String	jquery=.modal-dialog label:contains("${label}") tr:contains("${lang}") textarea
	Wait Until Page Contains Element		${sel}
	Input Text		${sel}		${translate}
User Set "${lang}" Translate: "${translate}" to "${label}" block on Modal Dialog TinyMCE
	#Wait Until Angular Ready
	#${sel}=		Convert To String	jquery=.modal-dialog label:contains("${label}") tr:contains("${lang}") textarea
	#Wait Until Page Contains Element		${sel}
	#Input Text		${sel}		${translate}
	Wait Until Angular Ready
	${sel}=		Convert To String	jquery=label:contains("${label}") tr:contains("${lang}") iframe
	Wait Until Page Contains Element		${sel}
	Select Frame						${sel}
	Press Key							css=body		\\01
	Press Key							css=body		${translate}
	[Teardown]	Unselect Frame
	
Robot Gets English Language from separated list
	[Arguments]		${string}
	${list}=		Split String		${string}		;
	${eng}=			Get From List		${list}		0
	[Return]		${eng}
	
Robot Can't find "${name}" Named row in table
	Wait Until Angular Ready
	${sel}=			Convert To String			jquery=tr:contains("${name}")
	Wait Until Page Does Not Contain Element	${sel}	3s

#Robot Helps Find the Correct Selector to Price Table Row Button
#	[Arguments]		${room_name}	${row}	${button}
#	Wait Until Angular Ready
#	${sel1}=	Convert To String	jquery=tbody:contains("${room_name}") th:contains("${row}") button:contains("${button}")
#	${sel2}=	Convert To String	jquery=tbody:contains("${room_name}") tr:contains("${row}") button:contains("${button}")
#	${contains1}=	Run Keyword And Return Status	Wait Until Page Contains Element	${sel1}		2s
#	Run Keyword If				'${contains1}' == 'True'	Log		Page Contains: ${sel1}
#	Return From Keyword If		'${contains1}' == 'True'	${sel1}
#	${contains2}=	Run Keyword And Return Status	Wait Until Page Contains Element	${sel2}		2s
#	Run Keyword If				'${contains2}' == 'True'	Log		Page Contains: ${sel2}
#	Return From Keyword If		'${contains2}' == 'True'	${sel2}
#	Return From Keyword			SELECTOR ERROR

Robot Helps in Drag and Drop to Numbered Position
	[Arguments]		${tr}	${dd}
	Sleep	2s
	${target}=		Convert To String	jquery=tr:eq(${dd})
	Wait Until Angular Ready
	Wait Until Page Contains Element	${tr}
	Wait Until Page Contains Element	${target}
	
	#${target_h_pos}=	Get Horizontal Position		${target}
	#${target_v_pos}=	Get Vertical Position		${target}
	#Log To Console		${target_h_pos}
	#Log To Console		${target_v_pos}
	#Drag And Drop		${tr}	${target}
	#Mouse Down			${tr}
	#Mouse Up			${target}
	
	#Drag And Drop By Offset		${tr}	300		300
	#Drag And Drop By Offset		${tr}	-200	-200
	
Robot Helps Uncheck all Possible Checkboxes
	[Arguments]		${row_name}
	${sel}=			Convert To String	jquery=tr:contains("${row_name}"):eq(1) input
	Wait Until Page Contains Element	${sel}
	${elements}=	Get Webelements		${sel}
	@{list}=		Convert To List		${elements}
	:FOR	${ELEMENT}	IN	@{list}
    \	Wait Until Page Contains Element	${ELEMENT}
	\	Unselect Checkbox					${ELEMENT}
	
Robot Helps Check Properties Inner Tab		
	[Arguments]		${str}
	
	@{list}=	Split String		${str}		;
	:FOR	${ELEMENT}	IN	@{list}
    \	User Click "${ELEMENT}" Property Inner Tab
	\	User Click "Add new" Link of "Child classifications" Block
	\	The "Classification editor" Modal Dialog is Active
	\	The "Classification editor" Modal Dialog Contains Expected Elements
	\	User Click Modal Dialog Cancel Button
	\	The "Classification editor" Modal Dialog is Inactive
	\	User Click "Add new" Link of "Child metas" Block
	\	The "Meta editor" Modal Dialog is Active
	\	The "Meta editor" Modal Dialog Contains Expected Elements
	\	User Click Modal Dialog Cancel Button
	\	The "Meta editor" Modal Dialog is Inactive

Robot Helps Find Selector
	[Arguments]		${string}
	Log 		${string}
	Log Dictionary		${SINGLE_SELECTORS}
	${search}=		Get From Dictionary		${SINGLE_SELECTORS}		${string}
	[Return]		${search}
	
Robot Helps Listing the Expected Elements
	[Arguments]		${string}
	@{search}=		Get From Dictionary		${GROUPED_SELECTORS}		${string}
	[Return]		${search}
	
Robot Helps to Create Device Dictionary
	#//th[@class="device" or @class="product" or @class="price-row"]
	#(//th[@class="device" or @class="product" or @class="price-row"])[6]
	${xpath}=			Convert To String	//th[@class="device" or @class="product" or @class="price-row"]
	${devices_dict}=	Create Dictionary
	#Wait Until Page Contains Element		jquery=.device, .product, .price-row
	Wait Until Page Contains Element		xpath=${xpath}
	#${elements}= 		Get Webelements		jquery=.device, .product, .price-row
	${elements}= 		Get Webelements		xpath=${xpath}
	${length}= 			Get Length			${elements}
	:FOR	${i}	IN RANGE 	${length}
	\	${c}= 			Evaluate	${i} + 1
	\	${selector}=	Convert To String	xpath=(${xpath})[${c}]
	\	${class}=		Get Element Attribute	${selector}@class
	#\	${text}=		Get Element Attribute	${selector}@text
	\	${text}=		Get Text				${selector}
	#\	${splitted}=	Split String	${text}		${\n}
	\	Log				${class}		WARN
	\	Log				${text}			WARN
	#\	Log				@{splitted}[0]			WARN
	#\	Assign Id To Element	${e}
	#\	${full_id}=		Get Element Attribute	${e}@id
	#\	Log				${e}	WARN
	[Return]			${devices_dict}

#Robot Helps Create List With Grouped Locators
#	${dict}=			Create Dictionary
#	${class_list}=		Create List
#	Append To List		${class_list}	${CLASS_ED_NAME_TABLE_JQ}	${CLASS_ED_NAME_ENG_IN_JQ}	${CLASS_ED_NAME_ENG_ADD_JQ}	${CLASS_ED_VALUE_TABLE_JQ}
#	...					${CLASS_ED_VALUE_ENG_IN_JQ}		${CLASS_ED_VALUE_ENG_ADD_JQ}	${CLASS_ED_CHARGE_TABLE_JQ}		${CLASS_ED_CHARGE_ENG_IN_JQ}
#	...					${CLASS_ED_CHARGE_ENG_ADD_JQ}	${CLASS_ED_DESC_TABLE_JQ}	${CLASS_ED_DESC_ENG_IN_JQ}	${CLASS_ED_HIGHLIGHT_SEL_JQ}
#	...					${CLASS_ED_LISTABLE_SEL_JQ}		${CLASS_ED_PRIORITY_IN_JQ}	${CLASS_ED_SAVE_BTN_JQ}		${CLASS_ED_CLEAR_BTN_JQ}
#	${meta_list}=		Create List
#	Append To List		${meta_list}	${META_ED_NAME_TABLE_JQ}	${META_ED_NAME_ENG_IN_JQ}	${META_ED_NAME_ENG_ADD_JQ}	${META_ED_VALUE_INPUT_JQ}	
#	...					${META_ED_DESC_TABLE_JQ}	${META_ED_DESC_ENG_IN_JQ}	${META_ED_LISTABLE_SEL_JQ}	${META_ED_PRIORITY_IN_JQ}	
#	...					${META_ED_SAVE_BTN_JQ}		${META_ED_CLEAR_BTN_JQ}
#	
#	Set To Dictionary	${dict}		Classification editor	${class_list}
#	Set To Dictionary	${dict}		Meta editor				${meta_list}
#	Set Global Variable		${GROUPED_SELECTORS}	${dict}
#Robot Helps Create List With Single Locators
#	${dict}=			Create Dictionary
#	Set To Dictionary	${dict}		Classification editor	${CLASSIFICATION_MODAL}
#	Set To Dictionary	${dict}		Meta editor				${META_MODAL}
#	Set Global Variable		${SINGLE_SELECTORS}	${dict}