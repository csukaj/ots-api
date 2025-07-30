*** Settings ***
Library					ExtendedSelenium2Library
Library					String
Library					Collections
Library					json
#Resource				resource_back_org.robot
#Resource				resource_basic_functions.robot
#Resource				resource_ddt.robot
#Resource				resource_helpers.robot
#Resource				resource_variables.robot

*** Keywords ***
Table rows greater than
	[Arguments]		${tr}	${n}
	${e}=			Get Webelements		${tr}
	${length}=		Get Length			${e}
	Should Be True	${length} > ${n}
	
User Select Availability Settings of "${hotel}"
	${stat}=		Run Keyword And Return Status
	...				Location Should Contain		organization/availabilities
	Run Keyword If	'${stat}' == 'False'	User Select Organizations then "${hotel}" then "Availabilities"
User Select Prices Settings of "${hotel}"
	${stat}=		Run Keyword And Return Status
	...				Location Should Contain		organization/list
	Run Keyword If	'${stat}' == 'False'	User Select Organizations then "${hotel}" then "Prices"
	
#Sub-template
User Set Hotel name with "${s_e_name}" name in "${s_e_lang}"
	Wait Until Angular Ready
	${sel}=				Convert To String		${GENERAL_S_NAME_LAN_INPUT_JQ} option:contains("${s_e_lang}")
	Input Text			${GENERAL_S_NAME_TXT_INPUT}		${s_e_name}
	Click Element		${sel}
	Click Element		${GENERAL_S_NAME_LAN_SET}
	
User Edit "${s_e_name_from}" Hotel name to "${s_e_name_to}" name in "${s_e_lang_to}"
	Wait Until Angular Ready
	${sel}=				Convert To String		${GENERAL_S_NAME_BLOCK_JQ} tr:contains("${s_e_name_from}") a:contains("Edit")
	Click Element		${sel}
	Input Text			${GENERAL_S_NAME_TXT_INPUT}		${s_e_name_to}
	${lang_sel}=		Convert To String		${GENERAL_S_NAME_LAN_INPUT_JQ} option:contains("${s_e_lang_to}")
	Click Element		${lang_sel}
	Click Element		${GENERAL_S_NAME_LAN_SET}
	
User Edit "${s_e_lang}" Language Hotel name to "${s_e_name_to}" name in "${s_e_lang_to}"
	Wait Until Angular Ready
	${sel}=				Convert To String		${GENERAL_S_NAME_BLOCK_JQ} tr:contains("${s_e_lang}") a:contains("Edit")
	Click Element		${sel}
	Input Text			${GENERAL_S_NAME_TXT_INPUT}		${s_e_name_to}
	${lang_sel}=		Convert To String		${GENERAL_S_NAME_LAN_INPUT_JQ} option:contains("${s_e_lang_to}")
	Click Element		${lang_sel}
	Click Element		${GENERAL_S_NAME_LAN_SET}
	
User Set Hotel Short Desc with "${sh_desc}" name in "${sh_desc_lang}"
	Wait Until Angular Ready
	${sel}=				Convert To String		${GENERAL_S_SH_DE_LAN_INP_JQ} option:contains("${sh_desc_lang}")
	Input Text			${GENERAL_S_SH_DE_TXT_INPUT}	${sh_desc}
	Click Element		${sel}
	Click Element		${GENERAL_S_SH_DE_LAN_SET}

User Edit "${sh_desc_lang}" Language Hotel Short Desc to "${sh_desc_to}" Short Desc in "${sh_desc_lang_to}"
	Wait Until Angular Ready
	${sel}=				Convert To String		${GENERAL_S_SH_BLOCK_JQ} tr:contains("${sh_desc_lang}") a:contains("Edit")
	Click Element		${sel}
	Input Text			${GENERAL_S_SH_DE_TXT_INPUT}		${sh_desc_to}
	${lang_sel}=		Convert To String		${GENERAL_S_SH_DE_LAN_INP_JQ} option:contains("${sh_desc_lang_to}")
	Click Element		${lang_sel}
	Click Element		${GENERAL_S_SH_DE_LAN_SET}
	
User Set Hotel Long Desc with "${lo_desc}" name in "${lo_desc_lang}"
	Wait Until Angular Ready
	${sel}=				Convert To String		${GENERAL_S_LO_DE_LAN_INP_JQ} option:contains("${lo_desc_lang}")
	Input Text			${GENERAL_S_LO_DE_TXT_INPUT}	${lo_desc}
	Click Element		${sel}
	Click Element		${GENERAL_S_LO_DE_LAN_SET}

User Edit "${lo_desc_lang}" Language Hotel Long Desc to "${lo_desc_to}" Long Desc in "${lo_desc_lang_to}"
	Wait Until Angular Ready
	${sel}=				Convert To String		${GENERAL_S_LO_BLOCK_JQ} tr:contains("${lo_desc_lang}") a:contains("Edit")
	Click Element		${sel}
	Input Text			${GENERAL_S_LO_DE_TXT_INPUT}		${lo_desc_to}
	${lang_sel}=		Convert To String		${GENERAL_S_LO_DE_LAN_INP_JQ} option:contains("${lo_desc_lang_to}")
	Click Element		${lang_sel}
	Click Element		${GENERAL_S_LO_DE_LAN_SET}
	
User Set "${gen_built_year}" to Hotel Built In Year
	Wait Until Angular Ready
	Input Text			${GENERAL_S_BUILT_IN_INPUT}		${gen_built_year}
	
User Set "${gen_ren_year}" to Hotel Renovation Year
	Wait Until Angular Ready
	Input Text			${GENERAL_S_RENOVATION_INPUT}	${gen_ren_year}
	
User Set "${stars}" Stars to Hotel
	Wait Until Angular Ready
	${sel}=				Convert To String		${GENERAL_S_STARS_INPUT_JQ} option:contains("${stars}")
	Click Element		${sel}
	
User Set "${hotel_type}" Hotel Type to Hotel
	Wait Until Angular Ready
	${sel}=				Convert To String		${GENERAL_S_HOTEL_TYPE_IN_JQ} option:contains("${hotel_type}")
	Click Element		${sel}
	
User Set Age Range with "${ar_name_fr}" name from "${ar_from_fr}" to "${ar_to_fr}"
	Wait Until Angular Ready
	Input Text			${AGE_RANGES_S_NAME_INPUT}		${ar_name_fr}
	Input Text			${AGE_RANGES_S_FROM_INPUT}		${ar_from_fr}
	Input Text			${AGE_RANGES_S_TO_INPUT}		${ar_to_fr}
	Click Button		${AGE_RANGES_S_SAVE_BTN}
	
User Edit "${ar_name_fr}" Named Age Range to "${ar_name_to}" Name from "${ar_from_to}" to "${ar_to_to}"
	Wait Until Angular Ready
	${sel}=				Convert To String		jquery=tr:contains("${ar_name_fr}") a:contains("Edit")
	Click Link			${sel}
	Input Text			${AGE_RANGES_S_NAME_INPUT}		${ar_name_to}
	Input Text			${AGE_RANGES_S_FROM_INPUT}		${ar_from_to}
	Input Text			${AGE_RANGES_S_TO_INPUT}		${ar_to_to}
	Click Button		${AGE_RANGES_S_SAVE_BTN}
	
User Delete "${ar_name_fr}" Named Age Range
	Wait Until Angular Ready	
	${sel}=				Convert To String		jquery=tr:contains("${ar_name_fr}") a:contains("Delete")
	Click Link			${sel}
	
User Set Closure Period from "${cl_fr}" to "${cl_to}" with "${cl_name}" Name
	Wait Until Angular Ready
	Sleep 	2s
	Press Key			${PERIODS_S_CLOS_FROM_INPUT}		${cl_fr}
	Press Key			${PERIODS_S_CLOS_TO_INPUT}			${cl_to}
	Input Text			${PERIODS_S_CLOS_NAME_INPUT}		${cl_name}
	Run Keyword And Ignore Error		Click Button		${PERIODS_S_CLOS_SAVE_BTN}

User Edit "${cl_name}" Named Closure Period to from "${cl_fr_to}" to "${cl_to_to}" with "${cl_name_to}" Name
	Wait Until Angular Ready
	${sel}=				Convert To String		jquery=div:contains("Closures") tr:contains("${cl_name}") a:contains("Edit")
	Click Link			${sel}
	Press Key			${PERIODS_S_CLOS_FROM_INPUT}		${cl_fr_to}
	Press Key			${PERIODS_S_CLOS_TO_INPUT}			${cl_to_to}
	Input Text			${PERIODS_S_CLOS_NAME_INPUT}		${cl_name_to}
	Sleep			1s
	Click Button		${PERIODS_S_CLOS_SAVE_BTN}
	
User Delete "${cl_name}" Named Closure Period
	Wait Until Angular Ready	
	${sel}=				Convert To String		jquery=div:contains("Closures") tr:contains("${cl_name}") a:contains("Delete")
	Click Link			${sel}
	
#PERIODS: 
User Set Open Period from "${op_fr}" to "${op_to}" with "${op_name}" Name and "${op_mn}" Minimum Nights and "${mpl}" Meal Plans
	Wait Until Angular Ready
	Press Key			${PERIODS_S_OP_FROM_INPUT}			${op_fr}
	Press Key			${PERIODS_S_OP_TO_INPUT}			${op_to}
	Input Text			${PERIODS_S_OP_NAME_INPUT}			${op_name}
	Input Text			${PERIODS_S_OP_MN_INPUT}			${op_mn}
	
	@{list}=			Split String		${mpl}	,
	:FOR    ${ELEMENT}    IN    @{list}
    \	${sel_ch}=				Convert To String		jquery=label:contains("Meal Plans") label:contains("${ELEMENT}") input
    \	Select Checkbox		${sel_ch}
	
	Sleep			1s
	Click Button		${PERIODS_S_OP_SAVE_BTN}
	
User Edit "${op_name}" Named Open Period to from "${op_fr_to}" to "${op_to_to}" with "${op_name_to}" Name and "${op_mn_to}" Minimum Nights and "${mpl_to}" Meal Plans
	
	${sel}=				Convert To String		jquery=div:contains("Open periods") tr:contains("${op_name}") a:contains("Edit")
	Click Link			${sel}
	
	Press Key			${PERIODS_S_OP_FROM_INPUT}			${op_fr_to}
	Press Key			${PERIODS_S_OP_TO_INPUT}			${op_to_to}
	Input Text			${PERIODS_S_OP_NAME_INPUT}			${op_name_to}
	Input Text			${PERIODS_S_OP_MN_INPUT}			${op_mn_to}
	
	@{list_def}=		Create List		e/p		b/b		h/b		f/b		inc
	:FOR    ${ELEMENT}    IN    @{list_def}
    \	${sel_ch}=			Convert To String		jquery=label:contains("Meal Plans") label:contains("${ELEMENT}") input
    \	Unselect Checkbox		${sel_ch}
	
	@{list}=			Split String		${mpl_to}	,
	:FOR    ${ELEMENT}    IN    @{list}
    \	${sel_ch}=			Convert To String		jquery=label:contains("Meal Plans") label:contains("${ELEMENT}") input
    \	Select Checkbox		${sel_ch}
	
	Sleep			1s
	Click Button		${PERIODS_S_OP_SAVE_BTN}
	
User Delete "${op_name}" Named Open Period
	Wait Until Angular Ready	
	${sel}=				Convert To String		jquery=div:contains("Open periods") tr:contains("${op_name}") a:contains("Delete")
	Click Link			${sel}
	
#OTHER OLD SUB-TEMP
User Set "${rn_name_fr}" Room Name in "${rn_lang_fr}" Language with "${r_am}" Amount and "${r_uag}" Age Group "${r_un}" Age Group Number
	Wait Until Angular Ready
	${sel}=				Convert To String		${ROOMS_S_NAME_LAN_INPUT_JQ} option:contains("${rn_lang_fr}")
	Input Text			${ROOMS_S_NAME_TXT_INPUT}	${rn_name_fr}
	Click Element		${sel}
	Click Element		${ROOMS_S_NAME_SET_BTN}
	
	Input Text			${ROOMS_S_AMMOUNT_INPUT}	${r_am}
	
	Click Element				${ROOMS_S_ADD_USAGE_BTN}
	Wait Until Angular Ready
	${sel_ag}=					Convert To String	jquery=label:contains("Age group") option:contains("${r_uag}")	
	Wait Until Page Contains Element	${sel_ag}
	Click Element				${sel_ag}
	Input text					${ROOMS_S_AU_NUMBER_INPUT}		${r_un}
	Click Element				${ROOMS_S_AU_SET_ROW}
	Click Element				${ROOMS_S_AU_SET_BTN}
	
	Sleep		2s
	Click Element		${ROOMS_S_SAVE}
	
User Edit "${rn_lang_fr}" Language Room to "${rn_name_to}" Room Name and "${rn_lang_to}" Language
	Wait Until Angular Ready
	${sel}=				Convert To String		jquery=label:contains("Name") tr:contains("${rn_lang_fr}") a:contains("Edit")
	Click Element		${sel}
	Input Text			${ROOMS_S_NAME_TXT_INPUT}		${rn_name_to}
	${lang_sel}=		Convert To String		${ROOMS_S_NAME_LAN_INPUT_JQ} option:contains("${rn_lang_fr}")
	Click Element		${lang_sel}
	Click Element		${ROOMS_S_NAME_SET_BTN}
	
User Delete "${rn_lang_fr}" Language Room
	Wait Until Angular Ready
	${sel}=				Convert To String		label:contains("Name") tr:contains("${rn_lang_fr}") a:contains("Delete")
	Click Element		${sel}
	
