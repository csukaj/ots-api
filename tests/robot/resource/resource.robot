*** Settings ***
Library					ExtendedSelenium2Library
Library					String
Library					Collections
Resource				../frontend/resource/resource_helpers.robot
Resource				../frontend/resource/resource_variables_languages.robot
Resource				../frontend/resource/resource_variables.robot

*** Variables ***
${BROWSER_NAME}			chrome
${REMOTE_URL}			http://127.0.0.1/wd/hub
${PLATFORM_NAME}		${EMPTY}
#${PLATFORM_NAME}		Android
${PLATFORM_VERSION}		${EMPTY}
#${PLATFORM_VERSION}	6.0.1
${DEVICE_NAME}			${EMPTY}
#${DEVICE_NAME}			33004d535bd282b7
#${BROWSER_X}			0
#${BROWSER_X}			0
${BROWSER_X}			-1900
#${BROWSER_Y}			0
#${BROWSER_Y}			-1000
${BROWSER_Y}			-300
${SPEED}				0
#${HOME}					http://ots.stylersdev.com/search;year=2026
${HOME}					http://ots.stylersdev.com
${SEARCH_HOME}			${HOME}/en/search;year=2026
${BLOG_LIST_HOME}		${HOME}/en/about-seychelles
${BACKEND_HOST}			http://admin.ots.stylersdev.com
${BACKEND_HOME}			${BACKEND_HOST}/home/login
${ADMIN_USER}			root@example.com
${ADMIN_PASS}			sdakfg8756HKSDGF


#DOM:
#NAVIGATION:
#${MY_HOLIDAY}			jquery=a:contains("My Holiday")
${MY_HOLIDAY}			css=#navbar > ul:nth-child(1) > li:nth-child(3) > a

#SEARCH:
${LANG_HU}				css=#navbar > ul.nav.navbar-nav.navbar-right > li:nth-child(1) > a
${LANG_EN}				css=#navbar > ul.nav.navbar-nav.navbar-right > li:nth-child(2) > a
${ARRIVAL_BTN}			css=button.btn:nth-child(1)
${DEPARTURE_BTN}		css=button.btn:nth-child(2)
${DATE_CLOSE}			css=.apply-btn
${DATE_NEXT_BTN}		css=.next
${DATE_PICKER}			css=.date-picker-wrapper
${DATE_PICKER_LEFT}		css=.month1
${SEARCH_BTN}			css=button.btn:nth-child(8)
${SEARCH_TEXT}			css=#text-search-input
${SEARCH_ADD_ROOM}		css=button.btn:nth-child(4)
${SEARCH_ADULTS}		css=#adult_count
${SEARCH_CHILDREN}		css=#children_count
${SEARCH_FIRST_HOTEL}	jquery=h3.clickable

#HOTEL:
${HOTEL_ROOM_DETAILS}	css=.room-details
${HOTEL_PRICE}			jquery=label:contains("€")
${HOTEL_ADD_HOLIDAY}	css=button.btn:nth-child(3)

#ADMIN:
#NAVIGATION:
#${ADMIN_MENU_ORG}		css=#navbar > ul > li:nth-child(3) > a:nth-child(1)
${ADMIN_MENU_ORG}		jquery=.navbar ul li a:contains("Accommodations")

#Login:
${ADMIN_INPUT_MAIL}		css=body > app-component > div > ng-component > div > form > div:nth-child(1) > input
${ADMIN_INPUT_PASS}		css=body > app-component > div > ng-component > div > form > div:nth-child(2) > input
${ADMIN_INPUT_SEND}		css=body > app-component > div > ng-component > div > form > button

*** Keywords ***
User Click Arrival Button
	Click Element			${ARRIVAL_BTN}

User Close Date Picker
	Click Element			${DATE_CLOSE}

User Find "${month}" Month

	${month}=
	...			Run Keyword If	'${BROWSER_NAME}'=='edge'
	...			Convert To Lowercase	${month}

	${see}=		Run Keyword And Return Status
	...		Wait Until Element Contains		${DATE_PICKER_LEFT}		${month}	timeout=0.5
	Log		${see}
	Run Keyword If	'${see}'=='False'
	...		User tap the next button when find	${month}

User tap the next button when find
	[Arguments]			${month}
	Click Element		${DATE_NEXT_BTN}
	User Find "${month}" Month

User Select "${n}" Date
	Click Element		jquery=div.day.toMonth.valid:contains("${n}")

User Select "${island}" Island
	Click Element		jquery=label:contains("${island}")

User Select "${mp}" Meal plans
	Click Element		jquery=label:contains("${mp}")

User Set "${n}" Adults
	Press Key			${SEARCH_ADULTS}	${n}

User Set "${n}" Children
	Press Key			${SEARCH_CHILDREN}	${n}

User Click Search Button
	Click Element		${SEARCH_BTN}

User Click First Hotel in Search List
	Wait Until Page Contains Element		${SEARCH_FIRST_HOTEL}
	Click Element							${SEARCH_FIRST_HOTEL}

User Select Base Hotel Price
	Scroll Element Into View			${HOTEL_ROOM_DETAILS}
	Click Element						${HOTEL_PRICE}

User Add Base Holiday
	Click Element		${HOTEL_ADD_HOLIDAY}

User Visit to My Holiday Page
	Click Element		${MY_HOLIDAY}

User Visit The OTS Site with "${bn}" browser
	Open Browser			${HOME}			${bn}
	Set Window Position		${BROWSER_X}	${BROWSER_Y}
	Maximize Browser Window
	Wait Until Angular Ready
	Robot Helps Detect the Site Language
User Visit The OTS Search Site with "${bn}" browser
	Open Browser			${SEARCH_HOME}			${bn}
	Set Window Position		${BROWSER_X}	${BROWSER_Y}
	Maximize Browser Window
	Wait Until Angular Ready
	Robot Helps Detect the Site Language
User Visit The OTS Backend with "${bn}" browser
	#${dict}				Create Dictionary
	#Set To Dictionary	${dict}		deviceName			${DEVICE_NAME}
	#Set To Dictionary	${dict}		platformVersion		${PLATFORM_VERSION}
	#Set To Dictionary	${dict}		platformName		${PLATFORM_NAME}
	#Log Dictionary		${dict}
	#Open Browser			${BACKEND_HOME}			${bn}	remote_url=${REMOTE_URL}	desired_capabilities=${dict}
	Open Browser			${BACKEND_HOME}			${bn}
	#DEMO SPEED :D
	Set Selenium Speed		${SPEED}
	Set Window Position		${BROWSER_X}	${BROWSER_Y}
	Maximize Browser Window
User Visit The OTS Blog List Site with "${bn}" browser
	Open Browser			${BLOG_LIST_HOME}			${bn}
    Set Window Position		${BROWSER_X}	${BROWSER_Y}
    Maximize Browser Window
    Wait Until Angular Ready
    Robot Helps Detect the Site Language

User Visit The OTS Site with IE browser
	Open Browser			${HOME}			ie	desired_capabilities=InternetExplorerDriver.INTRODUCE_FLAKINESS_BY_IGNORING_SECURITY_DOMAINS:true,ignoreZoomSetting:true
	#Set Window Position		${BROWSER_X}	${BROWSER_Y}
	#Maximize Browser Window
	Robot Helps Detect the Site Language
