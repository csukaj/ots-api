*** Settings ***
Library					ExtendedSelenium2Library
Library					String
Library					Collections
Library					json
Library  				JSONLibrary
Resource				resource_basic_functions.robot
Resource				resource_helpers.robot
#Resource				resource_navigations.robot
#Resource				resource_templates.robot
Resource				resource_variables.robot

*** Keywords ***
Conditional Navigation
	[Arguments]				${dict}
	Log Dictionary			${dict}
	#Robot Helps Write Dictionary to Json	${dict}		${OUTPUT_DIR}/test.json
	${url}=					Get From Dictionary		${dict}		url
	#Before:
	${before_stat}=			Robot Helps Run Commands from Dictionary	${dict}		before-keywords

	#Open or Goto:
	${opened_windows}= 		Run Keyword And Return Status    List Windows
	Run Keyword If			'${opened_windows}' == 'False' and '${before_stat}' == 'False'
	...						Open Browser And Maximize And Change Position	${url}	${BROWSER_NAME}		${BROWSER_X}	${BROWSER_Y}
	...		ELSE IF			'${opened_windows}' == 'True' and '${before_stat}' == 'False'
	...						Go To    ${url}

	#After:
	Robot Helps Run Commands from Dictionary	${dict}		after-keywords

Conditional Navigation From JSON
	[Arguments]		${json}
	${dict}=		Robot Helps Read Dictionary from Json	${json}
	Conditional Navigation		${dict}

Robot Helps Write Dictionary to Json
	[Arguments]			${dictionary_to_write}	${file_path}
	${json}=			Add Object To Json	${dictionary_to_write}	$	${dictionary_to_write}
	${json_as_str}=		Convert JSON To String	${json}
	Create File			${file_path}	${json_as_str}

Robot Helps Read Dictionary from Json
	[Arguments]			${file_path}
	${readed_dict}=		Load JSON From File		${file_path}
	[Return]			${readed_dict}

Robot Helps Run Commands from Dictionary
	[Arguments] 	${dictionary}	${key}
	${contains}=		Run Keyword And Return Status	Dictionary Should Contain Key	${dictionary}	${key}
	Return From Keyword If    '${contains}' == 'False'	True
	@{keywords_list}=		Get From Dictionary		${dictionary}		${key}
	${stat_list}=			Create List
	:FOR	${kw}	 IN		@{keywords_list}
	\	${stat}=	Run Keyword And Return Status
	\	...			Run Keywords		No Operation		AND		@{kw}
	\	${stat}=	Convert To String	${stat}
	\	Append To List    ${stat_list}	${stat}
	Log List			${stat_list}
	${all_passed}=		Run Keyword And Return Status
	...					List Should Not Contain Value	${stat_list}	False
	[Return]			${all_passed}

Open Browser And Maximize And Change Position
	[Arguments]				${url}	${brname}	${x}	${y}
	Open Browser			${url}	${brname}
    Set Window Position		${x}	${y}
    Maximize Browser Window
    Wait Until Angular Ready
    Robot Helps Detect the Site Language
