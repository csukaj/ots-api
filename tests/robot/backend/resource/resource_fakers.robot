*** Settings ***
Library					ExtendedSelenium2Library
Library					String
Library					Collections
Library					FakerLibrary
Library					json
Resource				resource_basic_functions.robot
Resource				resource_helpers.robot
Resource				resource_navigations.robot
Resource				resource_templates.robot
Resource				resource_variables.robot

*** Keywords ***
#DISCOUNTS
Robot Helps Set Discount Name With Fake Data
	${names}=	Create List
	${prefix}		Convert To String	Robot${SPACE}
	
	:FOR	${i}	IN RANGE	${4}
	\	${name}=	FakerLibrary.Text	16
	\	Append To List	${names}	${prefix}${name}
	
	${eng}=			Remove From List	${names}		0
	
	Wait Until Angular Ready
	Wait Until Page Contains Element	${DISC_ENG_NAME_INPUT_JQ}
	Input Text							${DISC_ENG_NAME_INPUT_JQ}	${eng}
	
	User Set Translates to Block	Name		${names}
	[Return]	${eng}
	
Robot Helps Select Modifier type by Fake Tips
	${sel_op}=		Convert To String		${DISC_MODIFIER_SEL} option
	${elements}=	Get Webelements			${sel_op}
	${length}= 		Get Length				${elements}
	${length}=		Evaluate				${length} - 1
	
	${rnd}= 		Random Int				0	${length}
	${sel}=			Convert To String		${sel_op}:eq(${rnd})
	Robot Helps Push The Button				${sel}
	${selected}=	Get Selected List Label		${DISC_MODIFIER_SEL}
	[Return]		${selected}
	
Robot Helps Check Discout Active Checkbox With Fake Option
	${bl}=		FakerLibrary.Boolean
	Run Keyword If	'${bl}' == 'True'	Robot Helps Select Checkbox		${DISC_ACTIVE_CHK_JQ}
	...				ELSE				Robot Helps Unselect Checkbox	${DISC_ACTIVE_CHK_JQ}
	${str}		Convert To String		${bl}
	${lower}=	Convert To Lowercase	${str}
	[Return]	${lower}
Robot Helps Check Discout Carry on Checkbox With Fake Option
	${bl}=		FakerLibrary.Boolean
	Run Keyword If	'${bl}' == 'True'	Robot Helps Select Checkbox		${DISC_CARRY_CHK_JQ}
	...				ELSE				Robot Helps Unselect Checkbox	${DISC_CARRY_CHK_JQ}
	${str}		Convert To String		${bl}
	${lower}=	Convert To Lowercase	${str}
	[Return]	${lower}
Robot Helps Select Discount Date Ranges With Fake Options
	${sel_op}=		Convert To String		${DISC_DATE_RANGES_SEL} option
	${elements}=	Get Webelements			${sel_op}
	${length}= 		Get Length				${elements}
	${indexes}=		Create List
	
	:FOR	${i}	IN RANGE	${length}
	\	${bl}=		FakerLibrary.Boolean
	\	${i_str}	Convert To String		${i}
	\	Run Keyword If	'${bl}' == 'True'	Append To List	${indexes}	${i_str}
	
	Run Keyword If 	${indexes} == @{EMPTY}	Append To List	${indexes}	0
	#Log 	${indexes}	WARN
	Select From List By Index	${DISC_DATE_RANGES_SEL}		@{indexes}

Robot Helps Set Promotion Code With Fake Data
	${word}=		FakerLibrary.Word
	${upper}=		Convert To Uppercase	${word}
	Robot Helps Write to Input with Jquery	${DISC_PROMOTION_INPUT}		${upper}
	[Return]		${upper}
Robot Helps Select Discount Condition by Fake Tips
	${sel_op}=		Convert To String		${DISC_CONDITION_SEL} option
	${elements}=	Get Webelements			${sel_op}
	${length}= 		Get Length				${elements}
	${length}=		Evaluate				${length} - 1
	
	${rnd}= 		Random Int				0	${length}
	${sel}=			Convert To String		${sel_op}:eq(${rnd})
	Robot Helps Push The Button				${sel}
	${selected}=	Get Selected List Label		${DISC_CONDITION_SEL}
	[Return]		${selected}
Robot Helps Select Discount Offer by Fake Tips
	${sel_op}=		Convert To String		${DISC_OFFER_SEL} option
	${elements}=	Get Webelements			${sel_op}
	${length}= 		Get Length				${elements}
	${length}=		Evaluate				${length} - 1
	
	${rnd}= 		Random Int				0	${length}
	${sel}=			Convert To String		${sel_op}:eq(${rnd})
	Robot Helps Push The Button				${sel}
	${selected}=	Get Selected List Label		${DISC_OFFER_SEL}
	[Return]		${selected}
Robot Helps Set Discount Offer Description With Fake Data
	${desc}=		Create List
	${prefix}		Convert To String	Robot${SPACE}
	
	:FOR	${i}	IN RANGE	${4}
	\	${d}=	FakerLibrary.Paragraph	2	false
	\	Append To List	${desc}	${prefix}${d}
	
	${eng}=			Remove From List	${desc}		0
	
	Wait Until Angular Ready
	Wait Until Page Contains Element	${DISC_OFFER_DESC_TMCE_JQ}
	#Input Text							${DISC_OFFER_DESC_JQ}	${eng}
	Select Frame						${DISC_OFFER_DESC_TMCE_JQ}
	Press Key							css=body		\\01
	Press Key							css=body		${eng}
	Unselect Frame
	
	User Set Translates to Block TinyMCE	Offer description		${desc}
	
Robot Helps Check Checkbox With Fake Option
	[Arguments]		${sel}
	${bl}=			FakerLibrary.Boolean
	Run Keyword If	'${bl}' == 'True'	Robot Helps Select Checkbox		${sel}
	...				ELSE				Robot Helps Unselect Checkbox	${sel}
	#${str}		Convert To String		${bl}
	#${lower}=	Convert To Lowercase	${str}
	#[Return]	${EMPTY}
	
Robot Helps Fill Input With Random Number
	[Arguments]		${sel}		${min}	${max}
	${rnd}= 		Random Int				${min}	${max}
	Robot Helps Write to Input with Jquery	${sel}	${rnd}
	#${filter}=		Convert To String	:contains("${rnd}")
	#[Return]		${filter}
	