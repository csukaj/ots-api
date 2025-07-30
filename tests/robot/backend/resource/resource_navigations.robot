*** Settings ***
Library					ExtendedSelenium2Library
Library					String
Library					Collections
Library					json
Resource				resource_basic_functions.robot
Resource				resource_helpers.robot
#Resource				resource_navigations.robot
Resource				resource_templates.robot
Resource				resource_variables.robot
*** Keywords ***
User Log In as Admin
	Input Text			${ADMIN_INPUT_MAIL}		${ADMIN_USER}
	Input Password		${ADMIN_INPUT_PASS}		${ADMIN_PASS}
	Click Button		${ADMIN_INPUT_SEND}
	Wait Until Angular Ready

User Select General tab
	#Scroll Element Into View			jquery=ng-component
	Click Link		${ORG_NAV_GENERAL_TAB}
User Select Location tab
	#Scroll Element Into View			jquery=ng-component
	Click Link		${ORG_NAV_LOCATION_TAB}
User Select Properties tab
	#Scroll Element Into View			jquery=ng-component
	Click Link		${ORG_NAV_PROPERTIES_TAB}
User Select Age Ranges tab
	#Scroll Element Into View			jquery=ng-component
	Click Link		${ORG_NAV_AGE_RANGES_TAB}
User Select Periods tab
	#Scroll Element Into View			jquery=ng-component
	Click Link		${ORG_NAV_PERIODS_TAB}
User Select Rooms tab
	#Scroll Element Into View			jquery=ng-component
	Click Link		${ORG_NAV_ROOMS_TAB}
User Select Availabilities tab
	#Scroll Element Into View		jquery=ng-component
	Robot Helps Push The Button		${ORG_NAV_AVAILABILITIES_TAB}
User Select Prices tab
	Click Link		${ORG_NAV_PRICES_TAB}
User Select Discounts tab
	Click Link		${ORG_NAV_DISCOUNTS_TAB}
User Select Discount Combinations tab
	Click Link		${ORG_NAV_DISCOUNT_COMBOS}
User Select Galleries tab
	Click Link		${ORG_NAV_GALLERIES}
User Click Add Usage Button
	Click Element	${ROOMS_ADD_USAGE_JQ}

User Select General Settings of "${hotel}"
	#${btn}=			Convert To String	jquery=tr:contains("${hotel}") .btn[title="General"]
	#${btn}=			Convert To String	jquery=tr:contains("${hotel}") .btn[title="Main"]
	#Wait Until Page Contains Element	${btn}
	#Click Element						${btn}
	User Select Organizations then "${hotel}" then "Main" with Tabs
	
User Click Add New Child Classification Button
	Wait Until Angular Ready
	Wait Until Page Contains Element	${PROPERTIES_CH_CLASS_ADD_JQ}
	Click Element						${PROPERTIES_CH_CLASS_ADD_JQ}
	
User Click Add New Child Meta Button
	Wait Until Angular Ready
	Wait Until Page Contains Element	${PROPERTIES_CH_META_ADD_JQ}
	Click Element						${PROPERTIES_CH_META_ADD_JQ}

User Click Add New Content Button
	Wait Until Angular Ready
	Wait Until Page Contains Element	${ADD_NEW_CONTENT_BUTTON}
	Click Element						${ADD_NEW_CONTENT_BUTTON}

User Click Add New Content Category Button
	Wait Until Angular Ready
	Wait Until Page Contains Element	${ADD_NEW_CONTENT_CATEGORY_BUTTON}
	Click Element						${ADD_NEW_CONTENT_CATEGORY_BUTTON}
	
User Select Organizations then "${hotel}" then "${sub}"
	User Select Organizations
	${btn}=			Convert To String	jquery=tr:contains("${hotel}") .btn[title="${sub}"]
	Wait Until Page Contains Element	${btn}
	Click Element						${btn}
	
User Select Organizations then "${hotel}" then "${sub}" with Tabs
	User Select Organizations
	#Wait Until Page Contains Element	jquery=navigation-component
	#Scroll Element Into View			jquery=app-breadcrumb-component
	#>Scroll Element Into View			css=body
	${select}=		Convert To String	jquery=td:contains("${hotel}")
	Robot Helps Push The Button			${select}
	${tab}=			Convert To String	jquery=ul.nav:eq(1) li:contains("${sub}")
	#Wait Until Page Contains Element	jquery=navigation-component
	#Scroll Element Into View			jquery=app-breadcrumb-component
	#>Scroll Element Into View			css=body
	#Log		I scrolled now...			WARN
	#Sleep	4s

	Sleep 		3s
	Robot Helps Push The Button			${tab}

User Select Organizations
	#Click Link			${ADMIN_MENU_ORG}
	#Wait Until Angular Ready
	Robot Helps Push The Button		${ADMIN_MENU_ORG}

User Select Contents
	Click Link			${MAIN_NAVIGATION_CONTENTS}
	Wait Until Angular Ready

User Select Content Categories
	Click Link			${CONTENT_NAVIGATION_CATEGORIES}
	Wait Until Angular Ready

User Click Main Cancel Button
	Click Element					${PROPERTIES_CLEAR_BTN_JQ}
	
User Click Modal Dialog Cancel Button
	Click Element					${CLASS_ED_CLEAR_BTN_JQ}

User Click "${row}" row "${button}" Button
	${original_speed}=		Get Selenium Speed
	Set Selenium Speed		0.1s
	Wait Until Angular Ready
	${sel}=			Convert To String		jquery=tr:contains("${row}") a:contains("${button}")
	Wait Until Page Contains Element		${sel}
	Click Element							${sel}
	Wait Until Angular Ready
	Set Selenium Speed		${original_speed}
	
User Click "${button}" Button of "${label}" Block
	${sel}=			Convert To String		jquery=label:contains("${label}") button:contains("${button}")
	Wait Until Page Contains Element		${sel}
	Click Element							${sel}
User Click "${button}" Link of "${label}" Block
	${sel}=			Convert To String		jquery=label:contains("${label}") a:contains("${button}")
	Wait Until Page Contains Element		${sel}
	Click Element							${sel}
	
User Click "${button}" Button of "${label}" Block on modal dialog
	${sel}=			Convert To String		jquery=.modal-dialog label:contains("${label}") button:contains("${button}")
	Wait Until Page Contains Element		${sel}
	Click Element							${sel}

User Select Single Room on Availabilities Page
	Click Element					${AVLBLTS_S_ROOM_SELECT} > option:nth-child(1)	
User Select Deluxe Room on Availabilities Page
	Click Element					${AVLBLTS_S_ROOM_SELECT} > option:nth-child(2)	
User Select Double Room on Availabilities Page
	Click Element					${AVLBLTS_S_ROOM_SELECT} > option:nth-child(3)
	
User Select the First Room on Rooms Page
	Robot Helps Push The Button		${ROOMS_FIRST_ROOM}
	
Navigate Back to Current Hotel on Breadcrumb
	Scroll Element Into View		jquery=body
	Robot Helps Push The Button		${BREADCRUMB_DEVICE}

#First start only-------------------------------------------------------------------------------------------------------
Navigate to Prices "${hotel_name}" Hotel Prices on First start only
	${sel}			Convert To String	jquery=ul[class="breadcrumb"] a:contains("${hotel_name}")
	${contains}		Run Keyword And Return Status	Page Should Contain Link	${sel}
	${actual_menu}	Run Keyword And Return Status	Location Should Contain		accommodation/prices
	#Run Keyword If	'${contains}' == 'False' or '${actual_menu}' == 'False'		User Select Organizations then "${hotel_name}" then "Prices"
	Run Keyword If	'${contains}' == 'False' or '${actual_menu}' == 'False'		User Select Organizations then "${hotel_name}" then "Prices" with Tabs
Navigate to "${hotel_name}" Discount Prices on First start only
	${sel}			Convert To String	jquery=ul[class="breadcrumb"] a:contains("${hotel_name}")
	${contains}		Run Keyword And Return Status	Page Should Contain Link	${sel}
	${actual_menu}	Run Keyword And Return Status	Location Should Contain		accommodation/prices/price_modified_accommodation
	#Run Keyword If	'${contains}' == 'False' or '${actual_menu}' == 'False'		User Select Organizations then "${hotel_name}" then "Discount Prices" with Tabs
	Run Keyword If	'${contains}' == 'False' or '${actual_menu}' == 'False'		User Select Organizations then "${hotel_name}" then "Modifier Prices" with Tabs
Navigate to Room Min. Nights "${hotel_name}" Hotel on First start only
	${sel}			Convert To String	jquery=ul[class="breadcrumb"] a:contains("${hotel_name}")
	${contains}		Run Keyword And Return Status	Page Should Contain Link	${sel}
	${actual_menu}	Run Keyword And Return Status	Location Should Contain		accommodation/minimum-nights
	#Run Keyword If	'${contains}' == 'False' or '${actual_menu}' == 'False'		User Select Organizations then "${hotel_name}" then "Room Min. Nights"
	Run Keyword If	'${contains}' == 'False' or '${actual_menu}' == 'False'		User Select Organizations then "${hotel_name}" then "Room Min. Nights" with Tabs
Navigate to Availabilities "${hotel_name}" Hotel on First start only
	${sel}			Convert To String	jquery=ul[class="breadcrumb"] a:contains("${hotel_name}")
	${contains}		Run Keyword And Return Status	Page Should Contain Link	${sel}
	${actual_menu}	Run Keyword And Return Status	Location Should Contain		accommodation/availabilities
	#Run Keyword If	'${contains}' == 'False' or '${actual_menu}' == 'False'		User Select Organizations then "${hotel_name}" then "Availabilities"
	Run Keyword If	'${contains}' == 'False' or '${actual_menu}' == 'False'		User Select Organizations then "${hotel_name}" then "Availabilities" with Tabs
Navigate to Properties "${hotel_name}" Hotel on First start only
	${sel}			Convert To String	jquery=ul[class="breadcrumb"] a:contains("${hotel_name}")
	${contains}		Run Keyword And Return Status	Page Should Contain Link	${sel}
	${actual_menu}	Run Keyword And Return Status	Location Should Contain		accommodation/properties
	#Run Keyword If	'${contains}' == 'False' or '${actual_menu}' == 'False'		User Select Organizations then "${hotel_name}" then "Properties"	
	Run Keyword If	'${contains}' == 'False' or '${actual_menu}' == 'False'		User Select Organizations then "${hotel_name}" then "Properties" with Tabs
Navigate to Periods "${hotel_name}" Hotel on First start only
	${sel}			Convert To String	jquery=ul[class="breadcrumb"] a:contains("${hotel_name}")
	${contains}		Run Keyword And Return Status	Page Should Contain Link	${sel}
	${actual_menu}	Run Keyword And Return Status	Location Should Contain		accommodation/date-ranges
	#Run Keyword If	'${contains}' == 'False' or '${actual_menu}' == 'False'		User Select Organizations then "${hotel_name}" then "Periods"
	Run Keyword If	'${contains}' == 'False' or '${actual_menu}' == 'False'		User Select Organizations then "${hotel_name}" then "Periods" with Tabs
Navigate to General "${hotel_name}" Hotel on First start only
	${sel}			Convert To String	jquery=ul[class="breadcrumb"] a:contains("${hotel_name}")
	${contains}		Run Keyword And Return Status	Page Should Contain Link	${sel}
	${attribute}=		Run Keyword And Ignore Error	
	...					Get Element Attribute	jquery=.nav-tabs li:eq(0)@class
	${active}=			Run Keyword And Return Status
	...					List Should Contain Value	${attribute}	active
	#Run Keyword If	'${contains}' == 'False' or '${active}' == 'False'		User Select Organizations then "${hotel_name}" then "General"
	#Run Keyword If	'${contains}' == 'False' or '${active}' == 'False'		User Select Organizations then "${hotel_name}" then "Main"
	Run Keyword If	'${contains}' == 'False' or '${active}' == 'False'		User Select Organizations then "${hotel_name}" then "Main" with Tabs
Navigate to Age Ranges "${hotel_name}" Hotel on First start only
	${sel}			Convert To String	jquery=ul[class="breadcrumb"] a:contains("${hotel_name}")
	${contains}		Run Keyword And Return Status	Page Should Contain Link	${sel}
	${actual_menu}	Run Keyword And Return Status	Location Should Contain		accommodation/age-ranges
	#Run Keyword If	'${contains}' == 'False' or '${actual_menu}' == 'False'		User Select Organizations then "${hotel_name}" then "Age Ranges"
	Run Keyword If	'${contains}' == 'False' or '${actual_menu}' == 'False'		User Select Organizations then "${hotel_name}" then "Age Ranges" with Tabs
Navigate to Rooms "${hotel_name}" Hotel on First start only
	${sel}			Convert To String	jquery=ul[class="breadcrumb"] a:contains("${hotel_name}")
	${contains}		Run Keyword And Return Status	Page Should Contain Link	${sel}
	${actual_menu}	Run Keyword And Return Status	Location Should Contain		accommodation/devices
	#Run Keyword If	'${contains}' == 'False' or '${actual_menu}' == 'False'		User Select Organizations then "${hotel_name}" then "Rooms"
	Run Keyword If	'${contains}' == 'False' or '${actual_menu}' == 'False'		User Select Organizations then "${hotel_name}" then "Rooms" with Tabs
Navigate to Discount Combinations "${hotel_name}" Hotel on First start only
	${sel}			Convert To String	jquery=ul[class="breadcrumb"] a:contains("${hotel_name}")
	${contains}		Run Keyword And Return Status	Page Should Contain Link	${sel}
	${actual_menu}	Run Keyword And Return Status	Location Should Contain		accommodation/price-modifier-combinations
	Run Keyword If	'${contains}' == 'False' or '${actual_menu}' == 'False'		User Select Organizations then "${hotel_name}" then "Modifier Combinations" with Tabs
	#Run Keyword If	'${contains}' == 'False' or '${actual_menu}' == 'False'		User Select Organizations then "${hotel_name}" then "Discounts"
Navigate to Location Page of "${hotel_name}" Hotel on First start only
	${sel}			Convert To String	jquery=ul[class="breadcrumb"] a:contains("${hotel_name}")
	${contains}		Run Keyword And Return Status	Page Should Contain Link	${sel}
	${actual_menu}	Run Keyword And Return Status	Location Should Contain		accommodation/location
	#Run Keyword If	'${contains}' == 'False' or '${actual_menu}' == 'False'		User Select Organizations then "${hotel_name}" then "Location"
	Run Keyword If	'${contains}' == 'False' or '${actual_menu}' == 'False'		User Select Organizations then "${hotel_name}" then "Location" with Tabs
Navigate to Gallery of "${hotel_name}" Hotel on First start only
	${sel}			Convert To String	jquery=ul[class="breadcrumb"] a:contains("${hotel_name}")
	${contains}		Run Keyword And Return Status	Page Should Contain Link	${sel}
	${actual_menu}	Run Keyword And Return Status	Location Should Contain		accommodation/galleries
	#Run Keyword If	'${contains}' == 'False' or '${actual_menu}' == 'False'		User Select Organizations then "${hotel_name}" then "Location"
	Run Keyword If	'${contains}' == 'False' or '${actual_menu}' == 'False'		User Select Organizations then "${hotel_name}" then "Gallery" with Tabs
Navigate to Discount of "${hotel_name}" Hotel on First start only
	${sel}			Convert To String	jquery=ul[class="breadcrumb"] a:contains("${hotel_name}")
	${contains}		Run Keyword And Return Status	Page Should Contain Link	${sel}
	${actual_menu}	Run Keyword And Return Status	Location Should Contain		accommodation/price-modifiers
	#Run Keyword If	'${contains}' == 'False' or '${actual_menu}' == 'False'		User Select Organizations then "${hotel_name}" then "Location"
	Run Keyword If	'${contains}' == 'False' or '${actual_menu}' == 'False'		User Select Organizations then "${hotel_name}" then "Price Modifiers" with Tabs
	
#\First start only------------------------------------------------------------------------------------------------------

Admin User Login And Navigate to Organization
	${stat}=		Run Keyword And Return Status
	...				Location Should Contain		accommodation
	Run Keyword If	'${stat}' == 'True'		Pass Execution	Location contains: 'accommodation'
	User Visit The OTS Backend with "${BROWSER_NAME}" browser
	User Log In as Admin
	User Select Organizations
	#Robot Helps Create List With Single Locators
	#Robot Helps Create List With Grouped Locators

Admin User Login And Navigate to Contents
	${stat}=		Run Keyword And Return Status
	...				Location Should Contain		content
	Run Keyword If	'${stat}' == 'True'		Pass Execution	Location contains: 'content'
	User Visit The OTS Backend with "${BROWSER_NAME}" browser
	User Log In as Admin
	User Select Contents

Admin User Login And Navigate to Content Categories
	${stat}=		Run Keyword And Return Status
	...				Location Should Contain		content/categories
	Run Keyword If	'${stat}' == 'True'		Pass Execution	Location contains: 'content/categories'
	User Visit The OTS Backend with "${BROWSER_NAME}" browser
	User Log In as Admin
	User Select Contents
	User Select Content Categories