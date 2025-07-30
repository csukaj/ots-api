*** Settings ***
Documentation			Backend Test for OTS Organizations
Resource				../../resource/resource.robot
Resource				../resource/resource_basic_functions.robot
Resource				../resource/resource_helpers.robot
Resource				../resource/resource_navigations.robot
Resource				../resource/resource_templates.robot
Resource				../resource/resource_variables.robot
Resource				../resource/resource_fakers.robot
Suite Setup				Admin User Login And Navigate to Organization
Default Tags			test_develop
#Test Teardown			Close Browser

*** Test Cases ***
Default Faker Case
	Navigate to Discount of "Hotel A" Hotel on First start only
	
	${name}=	Robot Helps Set Discount Name With Fake Data
	
	# Ide ki kell doldozni a Modifier type selectet!
	${modifier}=	Robot Helps Select Modifier type by Fake Tips
	
	${active}=	Robot Helps Check Discout Active Checkbox With Fake Option
	${carry_on}=	Robot Helps Check Discout Carry on Checkbox With Fake Option
	Robot Helps Select Discount Date Ranges With Fake Options
	${promo}=	Robot Helps Set Promotion Code With Fake Data
	${condition}=	Robot Helps Select Discount Condition by Fake Tips
	${offer}=	Robot Helps Select Discount Offer by Fake Tips
	Robot Helps Set Discount Offer Description With Fake Data
	
	Robot Helps Push The Button			${DISC_SAVE_BTN_JQ}
	
	${row}=			Convert To String
	#...		jquery=tr:has(td:eq(1):contains("${name}")):has(td:eq(2):contains("${promo}")):has(td:eq(4):contains("${condition}")):has(td:eq(6):contains("${offer}")):has(td:eq(8):contains("${active}")):has(td:eq(9):contains("${carry_on}"))
	...		jquery=tr:has(td:eq(1):contains("${name}")):has(td:eq(2):contains("${promo}")):has(td:eq(4):contains("${modifier}")):has(td:eq(5):contains("${condition}")):has(td:eq(7):contains("${offer}")):has(td:eq(9):contains("${active}")):has(td:eq(10):contains("${carry_on}"))
	
	#Log 	${row}		WARN
	Wait Until Page Contains Element	${row}
	
	#Edit
	${row_edit}= 	Convert To String	${row} a:contains("Edit")
	Robot Helps Push The Button		${row_edit}
	
	#Check the name :)
	Robot Helps Compare Input Value		${DISC_ENG_NAME_INPUT_JQ}	${name}
	
	#Enable hidden inputs:
	
	#Fill empty inputs		:visible kapcsoló az enable button miatt ->
	${all_inputs}			Get Webelements		jquery=.panel-default input:visible
	${all_inputs_length}	Get Length			${all_inputs}
	
	${first_block_inputs}	Get Webelements		jquery=.panel-default:eq(0) input:visible
	${fbl}					Get Length			${first_block_inputs}
	#Log 	FBL: ${fbl}		WARN
	
	${condition_prop_filter}=	Convert To String	${SPACE}td:eq(5)
	${offer_prop_filter}=		Convert To String	${SPACE}td:eq(7)
	
	:FOR	${i}	IN RANGE 	${all_inputs_length}
	\	${in_sel}=		Convert To String	jquery=.panel-default input:visible:eq(${i})
	\	${type}= 		Get Element Attribute	${in_sel}@type
	#\	Log To Console	type=${type}
	\	Run Keyword If		'${type}' == 'checkbox'		Robot Helps Check Checkbox With Fake Option		${in_sel}
	\	...		ELSE IF		'${type}' == 'text'			Robot Helps Fill Input With Random Number		${in_sel}	${1}	${100}
	#\	Log		${filter}		WARN
	
	#Set Minimum Values
	${minimum_inputs}= 		Get Webelements		jquery=.panel-default label:contains("inimum") input:visible
	${minimum_length}= 		Get Length			${minimum_inputs}
	
	:FOR	${i}	IN RANGE	${minimum_length}
	#\	${i_str}	Convert To String		${i}
	\	${min_sel}	Convert To String		jquery=.panel-default label:contains("inimum") input:visible:eq(${i})
	\	${min_val}	Random Int				1	10
	\	Robot Helps Write to Input with Jquery	${min_sel}		${min_val}
	
	#Set Maximum Values
	${maximum_inputs}= 		Get Webelements		jquery=.panel-default label:contains("aximum") input:visible
	${maximum_length}= 		Get Length			${maximum_inputs}
	
	:FOR	${i}	IN RANGE	${maximum_length}
	#\	${i_str}	Convert To String		${i}
	\	${max_sel}	Convert To String		jquery=.panel-default label:contains("aximum") input:visible:eq(${i})
	\	${max_val}	Random Int				11	20
	\	Robot Helps Write to Input with Jquery	${max_sel}		${max_val}
	
	#Robot Helps Push The Button		${GENERAL_SAVE_JQ}
	
	#Check the saved results:
	#tr:has(td:eq(1):contains("Robot Nesciunt quae.")):has(td:eq(2):contains("MAXIME")):has(td:eq(4):contains("Early bird fixed date")):has(td:eq(6):contains("Free nights")):has(td:eq(8):contains("true")):has(td:eq(9):contains("false")) td:eq(5):contains("6"):contains("19")
	#vagy ...
	
	#${condition_prop_cell}=		Convert To String	${EMPTY}
	
	:FOR	${i}	IN RANGE 	${all_inputs_length}
	\	${in_sel}=		Convert To String	jquery=.panel-default input:visible:eq(${i})
	\	${label_sel}=	Convert To String	jquery=.panel-default label:not(:has(label)):visible:eq(${i})
	\	${label_val}=	Get Text	${label_sel}
	\	${in_val}=		Get Value	${in_sel}
	\	${type}= 		Get Element Attribute	${in_sel}@type
	#\	Log To Console	type=${type}
	\	${filter}= 	Convert To String 	${EMPTY}
	\	${filter}=	Run Keyword If		'${type}' == 'text'		Convert To String	:contains("${label_val}: ${in_val}")
	#\	Log		${filter}	WARN
	#\	${condition_prop_filter}=	
	#\	...		Run Keyword If			${i} < ${fbl} and '${type}' == 'text'	Convert To String	${condition_prop_filter}${filter}
	\	${condition_prop_filter}=		Run Keyword If			${i} < ${fbl} and '${type}' == 'text'	Catenate	SEPARATOR=	${condition_prop_filter}	${filter}
	\	...		ELSE					Catenate	SEPARATOR=	${condition_prop_filter}	${EMPTY}
	\	${offer_prop_filter}=			Run Keyword If			${i} >= ${fbl} and '${type}' == 'text'	Catenate	SEPARATOR=	${offer_prop_filter}	${filter}
	\	...		ELSE					Catenate	SEPARATOR=	${offer_prop_filter}	${EMPTY}
	
	#Log		condition_prop_filter: ${condition_prop_filter}		WARN
	#Log		offer_prop_filter: ${offer_prop_filter}				WARN
	
	${condition_cell}= 		Convert To String	${row}${condition_prop_filter}
	${offer_cell}= 			Convert To String	${row}${offer_prop_filter}
	
	Robot Helps Push The Button		${GENERAL_SAVE_JQ}
	
	#Log		${condition_cell}	WARN
	#Log		${offer_cell}		WARN
	
	Wait Until Page Contains Element	${condition_cell}
	Wait Until Page Contains Element	${offer_cell}