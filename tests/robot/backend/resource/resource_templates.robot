*** Settings ***
Library					ExtendedSelenium2Library
Library					String
Library					Collections
Library					json
Resource				resource_basic_functions.robot
Resource				resource_helpers.robot
Resource				resource_navigations.robot
#Resource				resource_templates.robot
Resource				resource_variables.robot

*** Keywords ***
# TEMPLATES----------------------------------------------------------------------------------------------------TEMPLATES

#ORGANIZATIONS
Create Hotel template
	[Arguments]		${name}     ${category}		${discount}		${mfn}
	User Create Hotel with "${name}" name with "${category}" and discount:"${discount}" and Merged free nights "${mfn}"

#GENERAL
General template
#					Hotel Name		Parent Org		Active		Short description	Long description
	[Arguments]		${hotel_name}	${parent_org}	${active}	${short_desc}		${long_desc}
	${hotel}=			Robot Gets English Language from separated list		${hotel_name}
	
	Navigate to General "${hotel}" Hotel on First start only
	User Edit General Settings of "${hotel_name}" Named Hotel with Short Description: "${short_desc}"; Long Description: "${long_desc}"; Activity is Active: "${active}" Parent Org: "${parent_org}"

Content template
	[Arguments]		${type}     ${title}	${lead}		${content}		${meta_description} 	${meta_keyword}     ${meta_title}       ${status}

	User Select Contents
	Run Keyword If		'${type}' == 'New'          User Create Content with; Title: "${title}" Lead: "${lead}" Content: "${content}" Meta Description: "${meta_description}" Meta Keyword: "${meta_keyword}" Meta Title: "${meta_title}" Status: "${status}"
    	...		ELSE IF		'${type}' == 'Edit'		User Edit Content with; Title: "${title}" Lead: "${lead}" Content: "${content}" Meta Description: "${meta_description}" Meta Keyword: "${meta_keyword}" Meta Title: "${meta_title}" Status: "${status}"
    	...		ELSE IF		'${type}' == 'Delete'	User Delete Content "${title}"

Content Category template
	[Arguments]		${type}     ${en}     ${de}     ${hu}       ${ru}

	User Select Contents
	User Select Content Categories
	Run Keyword If		'${type}' == 'New'          User Create Content Category with; English: "${en}" German: "${de}" Hungarian: "${hu}" Russiona: "${ru}"
    	...		ELSE IF		'${type}' == 'Edit'		User Edit Content Category with; English: "${en}" German: "${de}" Hungarian: "${hu}" Russiona: "${ru}"
    	...		ELSE IF		'${type}' == 'Delete'	User Delete Content Category "${en}"


#LOCATION
Location template
	[Arguments]		${hotel_name}	${island}	${district}		${latitude}		${longitude}	${po}
	
	User Set Location to "${hotel_name}" Hotel; Island: "${island}", District: "${district}", Latitude: "${latitude}", Longitude: "${longitude}", P.O. Box: "${po}" 

#PROPERTIES
Properties templete
	#				Hotel		New/Edit/Delete		Tab		Meta/Class
	[Arguments]		${hotel_name}		${type}		${tab}	${label}
	#	Child/		Name;;;		Searchable	Name Icon	Value;;;	Value Icon	Desc;;;		Charge	HL	Listable	Prio
	#	Meta/		Name;;;		Name Icon	Value;;;	Desc;;;		Listable	Prio
	...				@{other_args}
	
	@{temp}=			Create List		${EMPTY}	${EMPTY}	${EMPTY}	${EMPTY}	${EMPTY}	${EMPTY}	${EMPTY}	${EMPTY}	${EMPTY}	${EMPTY}	${EMPTY}
	@{other_args}=		Combine Lists	${other_args}	${temp}
	
	
	Run Keyword If		'${label}' == 'Child classification' and '${type}' == 'New'
	...		User Create Child classification In "${hotel_name}" Hotel's "${tab}" Category; Name is: "@{other_args}[0]", Searchable: "@{other_args}[1]", Name Icon: "@{other_args}[2]", Value: "@{other_args}[3]", Value Icon: "@{other_args}[4]", Description: "@{other_args}[5]", Charge: "@{other_args}[6]", Charge Icon: "@{other_args}[7]", Highlighted: "@{other_args}[8]", Listable: "@{other_args}[9]", Priority: "@{other_args}[10]"
	...		ELSE IF		'${label}' == 'Child classification' and '${type}' == 'Edit'
	...		User Edit Child classification In "${hotel_name}" Hotel's "${tab}" Category; Name is: "@{other_args}[0]", Searchable: "@{other_args}[1]", Name Icon: "@{other_args}[2]", Value: "@{other_args}[3]", Value Icon: "@{other_args}[4]", Description: "@{other_args}[5]", Charge: "@{other_args}[6]", Charge Icon: "@{other_args}[7]", Highlighted: "@{other_args}[8]", Listable: "@{other_args}[9]", Priority: "@{other_args}[10]"
	...		ELSE IF		'${label}' == 'Child classification' and '${type}' == 'Delete'
	...		User Delete Child classification from "${hotel_name}" Hotel's "${tab}" Category; Name is: "@{other_args}[0]"
	...		ELSE IF		'${label}' == 'Child meta' and '${type}' == 'New'
	...		User Create Child meta In "${hotel_name}" Hotel's "${tab}" Category; Name is: "@{other_args}[0]", Name Icon: "@{other_args}[1]", Value: "@{other_args}[2]", Description: "@{other_args}[3]", Listable: "@{other_args}[4]", Priority: "@{other_args}[5]"
	...		ELSE IF		'${label}' == 'Child meta' and '${type}' == 'Edit'
	...		User Edit Child meta In "${hotel_name}" Hotel's "${tab}" Category; Name is: "@{other_args}[0]", Name Icon: "@{other_args}[1]", Value: "@{other_args}[2]", Description: "@{other_args}[3]", Listable: "@{other_args}[4]", Priority: "@{other_args}[5]"
	...		ELSE IF		'${label}' == 'Child meta' and '${type}' == 'Delete'
	...		User Delete Child meta from "${hotel_name}" Hotel's "${tab}" Category; Name is: "@{other_args}[0]"
	
#AGE RANGES
Age Ranges template
#					Hotel Name		N/E/D		Name	From	To
	[Arguments]		${hotel_name}	${type}		${name}		${from}		${to}	${banned}	${free}
	
	Navigate to Age Ranges "${hotel_name}" Hotel on First start only
	
	Run Keyword If		'${type}' == 'New'		User Create Age Range to "${hotel_name}" hotel with; Name: "${name}" From: "${from}" To: "${to}" Banned: "${banned}" Free: "${free}"
	...		ELSE IF		'${type}' == 'Edit'		User Edit Age Range to "${hotel_name}" hotel with; Name: "${name}" From: "${from}" To: "${to}" Banned: "${banned}" Free: "${free}"
	...		ELSE IF		'${type}' == 'Delete'	User Delete "${name}" Age Range from "${hotel_name}" hotel

#PERIODS
Periods template
#					Hotel Name		N/E/D		Category	@Name	@From		@To		@MINN	@MP
	[Arguments]		${hotel_name}	${type}		${category}		@{other_args}
	@{temp}=			Create List		${EMPTY}	${EMPTY}	${EMPTY}	${EMPTY}	${EMPTY}
	@{other_args}=		Combine Lists	${other_args}	${temp}
	Run Keyword If		'${type}' == 'New'			User Create "${category}" Period to "${hotel_name}" Hotel; Name: "@{other_args}[0]", From: "@{other_args}[1]", To: "@{other_args}[2]", Minimum nights: "@{other_args}[3]", Meal Plans: "@{other_args}[4]"
	...		ELSE IF		'${type}' == 'Edit'			User Edit "${category}" Period in "${hotel_name}" Hotel; Name: "@{other_args}[0]", From: "@{other_args}[1]", To: "@{other_args}[2]", Minimum nights: "@{other_args}[3]", Meal Plans: "@{other_args}[4]"
	...		ELSE IF		'${type}' == 'Delete'		User Delete "${category}" Period from "${hotel_name}" Hotel; Name: "@{other_args}[0]"

#ROOMS
Rooms template
#					Hotel Name		stat		Room Name	Amount
	[Arguments]		${hotel_name}	${type}		${name}		${amount}
	
	Navigate to Rooms "${hotel_name}" Hotel on First start only
	
	Run Keyword If		'${type}' == 'New'		User Create Room to "${hotel_name}" hotel with; Name: "${name}" Amount: "${amount}"
	...		ELSE IF		'${type}' == 'Edit'		User Edit Room to "${hotel_name}" hotel with; Name: "${name}" Amount: "${amount}"
	...		ELSE IF		'${type}' == 'Delete'	User Delete "${name}" Room from "${hotel_name}" hotel
Rooms Add Usage template
#					Hotel Name		stat		Room Name	Age Group	Number
	[Arguments]		${hotel_name}	${type}		${name}		${age}		${number}	${age_to}		${number_to}
	${name}=		Robot Gets English Language from separated list		${name}
	Given Navigate to Rooms "${hotel_name}" Hotel on First start only
	And User Click "${name}" row "Edit" Button
	
	Run Keyword If		'${type}' == 'New'		User Create Room Usage with; Age group: "${age}" and Number: "${number}"
	...		ELSE IF		'${type}' == 'Edit'		User Edit Room Usage with; Age group: "${age}" and Number: "${number}" to: "${age_to}" and Number: "${number_to}"
	...		ELSE IF		'${type}' == 'Delete'	User Delete "${name}" Room Usage where: Age group: "${age}" and Number: "${number}"
	
	User Click Room Save Button

#ROOM MINIMUM NIGTHS
Room Minimum Nights Template
	[Arguments]		${hotel_name}	${room_name}	${dates}	${value}
	Navigate to Room Min. Nights "${hotel_name}" Hotel on First start only
	User Set Minimum Nights One by One: Room Name: "${room_name}" Dates: "${dates}" Values: "${value}"
	Robot Helps Push The Button		${ROOM_MIN_N_SAVE_BTN}
Room Minimum Nights Grouped Template
#					Hotel Name		Room Name		Enable/Disable/Set		Dates	Value
	[Arguments]		${hotel_name}	${room_name}	${status}	${dates}	${value}
	Navigate to Room Min. Nights "${hotel_name}" Hotel on First start only
	User Select Multiple Cells in Room "${room_name}" by Date "${dates}"
	Run Keyword If		'${status}' == 'Set'		User Set Common Value: "${value}"
	... 	ELSE IF		'${status}' == 'Enable'		Robot Helps Push The Button		${ROOM_MIN_N_ENABLE_BTN}
	... 	ELSE IF		'${status}' == 'Disable'	Robot Helps Push The Button		${ROOM_MIN_N_DISABLE_BTN}
	Robot Helps Push The Button		${ROOM_MIN_N_SAVE_BTN}
	
#AVAILABILITIES
Availabilities Section Template
	[Arguments]		${hotel_name}	${av_stat}		${av_room}	${av_year}	${av_month}	 	${av_days}
	
	Navigate to Availabilities "${hotel_name}" Hotel on First start only
	User Change Availability "${av_stat}" to "${av_room}" Room "${av_year}" Year and "${av_month}" Month and "${av_days}" Days

#PRICES
Prices Section Template
	[Arguments]		${hotel_name}	${room_name}	${row_name}		${date_period}	${meal_plan}	 ${input_type}	${value}
	
	Navigate to Prices "${hotel_name}" Hotel Prices on First start only	
	User Edit Price table of "${hotel_name}" Hotel of "${room_name}" room and "${row_name}" row and "${date_period}" date period and "${meal_plan}" meal plan to "${input_type}": "${value}" value
	
Prices Section Margin Template
	[Arguments]		${hotel_name}	${margin_key}	${margin_value}
	
	Navigate to Prices "${hotel_name}" Hotel Prices on First start only
	User Edit Main Margin Value of "${hotel_name}" Hotel with "${margin_key}" Key: "${margin_value}" Value

Price Row Template
	[Arguments]		${hotel_name}	${room_name}	${stat}		${row_name}		${age}	${amount}	${extra}
		
	Navigate to Prices "${hotel_name}" Hotel Prices on First start only
	
	Run Keyword If 		'${stat}' == 'New'		User Add Price Row to "${hotel_name}" Hotel, "${room_name}" Room with Name: "${row_name}", Age Range: "${age}", Amount: "${amount}" and Extra is "${extra}"
	...		ELSE IF		'${stat}' == 'Edit'		User Edit Price Row in "${hotel_name}" Hotel, "${room_name}" Room Name: "${row_name}", Age Range: "${age}", Amount: "${amount}" and Extra is "${extra}"
	...		ELSE IF		'${stat}' == 'Delete'	User Delete "${row_name}" Named Price Row from "${room_name}" Named Room Name
	
#DISCOUNTS


#DISCOUNT PRICES
Discount Prices Section Template
	[Arguments]		${hotel_name}	${room_name}	${row_name}		${date_period}	${meal_plan}	 ${input_type}	${value}
	
	Navigate to "${hotel_name}" Discount Prices on First start only	
	User Edit Price table of "${hotel_name}" Hotel of "${room_name}" room and "${row_name}" row and "${date_period}" date period and "${meal_plan}" meal plan to "${input_type}": "${value}" value
	
Discount Prices Section Margin Template
	[Arguments]		${hotel_name}	${margin_key}	${margin_value}
	
	Navigate to "${hotel_name}" Discount Prices on First start only
	User Edit Main Margin Value of "${hotel_name}" Hotel with "${margin_key}" Key: "${margin_value}" Value

Discount Price Row Template
	[Arguments]		${hotel_name}	${room_name}	${stat}		${row_name}		${age}	${amount}	${extra}
		
	Navigate to "${hotel_name}" Discount Prices on First start only
	
	Run Keyword If 		'${stat}' == 'New'		User Add Price Row to "${hotel_name}" Hotel, "${room_name}" Room with Name: "${row_name}", Age Range: "${age}", Amount: "${amount}" and Extra is "${extra}"
	...		ELSE IF		'${stat}' == 'Edit'		User Edit Price Row in "${hotel_name}" Hotel, "${room_name}" Room Name: "${row_name}", Age Range: "${age}", Amount: "${amount}" and Extra is "${extra}"
	...		ELSE IF		'${stat}' == 'Delete'	User Delete "${row_name}" Named Price Row from "${room_name}" Named Room Name


#DISCOUNT COMBINATIONS
Discount Combinations Template
	#						Hotel Name	Row name	Column Names	d&d
	[Arguments]		${hotel_name}	${row_name}		${columns}		${dd}
	
	Navigate to Discount Combinations "${hotel_name}" Hotel on First start only
	User Edit "${row_name}" Discount Combination Row; Select Checkboxes: "${columns}" and drag to "${dd}"
	
#GALLERY
Image Template
	#[Arguments]		${hotel_name}	${stat}		${path}
	[Arguments]		@{arguments}
	@{temp}=			Create List		${EMPTY}	${EMPTY}	${EMPTY}	${EMPTY}	${EMPTY}	${EMPTY}	${EMPTY}	${EMPTY}	${EMPTY}	${EMPTY}	${EMPTY}
	@{arguments}=		Combine Lists	${arguments}	${temp}
	#Log		@{arguments}[2]		WARN
	Navigate to Gallery of "@{arguments}[0]" Hotel on First start only
	Run Keyword If 		'@{arguments}[1]' == 'Upload'	User Upload Image(s)	 @{arguments}[2]
	...		ELSE IF		'@{arguments}[1]' == 'Edit'		User Edit Image: "@{arguments}[2]"; Description: "@{arguments}[3]"; Highlighted: "@{arguments}[4]"
	...		ELSE IF		'@{arguments}[1]' == 'Delete'	User Delete "@{arguments}[2]" Image
	...		ELSE IF		'@{arguments}[1]' == 'Properties'	User Set Gallery Properties; Name: "@{arguments}[2]", Role: "@{arguments}[3]"
	

# SUB-TEMPLATES--------------------------------------------------------------------------------------------SUB-TEMPLATES

#ORGANIZATIONS
User Create Hotel with "${name}" name with "${category}" and discount:"${discount}" and Merged free nights "${mfn}"
	Wait Until Angular Ready
	Click Element						${ORG_MAIN_ADD_NEW}
	Wait Until Page Contains Element	${ORG_MAIN_NEW_HOTEL_DIALOG}
	Input Text							${ORG_MAIN_NH_NAME_INPUT}	${name}
	Robot Helps Select an Option with Jquery	${ORG_MAIN_NEW_HOTEL_CATEGORY_SELECT}	${category}
	Robot Helps Select an Option with Jquery	${ORG_MAIN_NEW_HOTEL_DISCOUNT_SELECT}	${discount}
	Robot Helps Select an Option with Jquery	${ORG_MAIN_NEW_HOTEL_MFN_SELECT}	${mfn}
	#Wait Until Element Does Not Contain Attribute	${GENERAL_SAVE_JQ}	disabled
	Wait Until Element Is Enabled		${GENERAL_SAVE_JQ}
	Click Element						${GENERAL_SAVE_JQ}
	
	${alert}=	Run Keyword and Return Status
	...			Robot Should Get a Waited Modal Alert with This Message: "${ALERT_ORG_EXIST}" and Press "OK"			
	Return From Keyword If	'${alert}' == 'True'
	
	Wait Until Element Contains			${ORG_MAIN_TABLE}			${name}

#GENERAL
User Edit General Settings of "${hotel_name}" Named Hotel with Short Description: "${short_desc}"; Long Description: "${long_desc}"; Activity is Active: "${active}" Parent Org: "${parent_org}"
	Wait Until Angular Ready
	When User Set General Hotel Activity; Is Active: "${active}"
	And User Set General Hotel Parent Organization: "${parent_org}"
	And User Set General Hotel Name: "${hotel_name}"
	And User Set General Hotel Short Description: "${short_desc}"
	And User Set General Hotel Long Description: "${long_desc}"
	And User Click General Save Button
	${alert}=	Run Keyword and Return Status
	...			Robot Should Get a Waited Modal Alert with This Message: "${ALERT_ORG_EXIST}" and Press "OK"
	Return From Keyword If	'${alert}' == 'True'
	#Sleep	2s
	#Wait Until Angular Ready
	#Then Robot Compare General Hotel Activity; Is Active: "${active}"
	#And Robot Compare General Hotel Name: "${hotel_name}"
	#And Robot Compare General Hotel Short Description: "${short_desc}"
	#And Robot Compare General Hotel Long Description: "${long_desc}"

#LOCATION:
User Set Location to "${hotel_name}" Hotel; Island: "${island}", District: "${district}", Latitude: "${latitude}", Longitude: "${longitude}", P.O. Box: "${po}"
	Given Navigate to Location Page of "${hotel_name}" Hotel on First start only
	Then The Location Page Contains the Expected Elements
	When User Set Island: "${island}"
	And User Set District: "${district}"
	And User Set Latitude: "${latitude}"
	And User Set Longitude: "${longitude}"
	And User Set P.O. Box: "${po}"
	And User Click Location Save Button
	Then Robot Compare Location; Island: "${island}"
	And Robot Compare Location; District: "${district}"
	And Robot Compare Location; Latitude: "${latitude}"
	And Robot Compare Location; Longitude: "${longitude}"
	And Robot Compare Location; P.O. Box: "${po}"

#PROPERTIES
#CHILD CLASS
User Create Child classification In "${hotel_name}" Hotel's "${tab}" Category; Name is: "${name}", Searchable: "${searchable}", Name Icon: "${name_icon}", Value: "${value}", Value Icon: "${value_icon}", Description: "${desc}", Charge: "${charge}", Charge Icon: "${charge_icon}", Highlighted: "${hl}", Listable: "${listable}", Priority: "${prio}"
	${eng_name}=		Robot Gets English Language from separated list		${name}
	${eng_value}=		Robot Gets English Language from separated list		${value}
	${eng_desc}=		Robot Gets English Language from separated list		${desc}
	${eng_charge}=		Robot Gets English Language from separated list		${charge}
	Given Navigate to Properties "${hotel_name}" Hotel on First start only
	And User Click "${tab}" Property Inner Tab
	And User Click "Add new" Link of "Child classifications" Block
	When User Set New Child Class Name: "${name}"
	And User Set New Child Class Searchable: "${searchable}"
	And User Set Child Class Name Icon: "${name_icon}"
	And User Set New Child Class Value: "${value}"
	And User Set Child Class Value Icon: "${value_icon}"
	#And User Set Child Class Description: "${desc}"
	And User Set Child Class Description to TinyMCE: "${desc}"
	And User Set New Child Class Charge: "${charge}"
	And User Set Child Class Charge Icon: "${charge_icon}"
	And User Set Child Class Highlight: "${hl}"
	And User Set Child Class Listable: "${listable}"
	And User Set Child Class Priority: "${prio}"
	And User Click Child Class Save Button
	#Sleep	2s
	${alert}=	Run Keyword and Return Status
	...			Robot Should Get a Waited Modal Alert with This Message: "${ALERT_TAX_EXIST}" and Press "OK"			
	#Run Keyword If 			'${alert}' == 'True'	User Click Property Clear Button
	${alert_prop}=	Run Keyword and Return Status
	...			Robot Should Get a Waited Modal Alert with This Message: "${ALERT_PROP_EXIST}" and Press "OK"			
	Return From Keyword If	'${alert}' == 'True' or '${alert_prop}' == 'True'
	
	Then Robot Check "${eng_name}" Row Name Column of Child Class Table value is "${eng_name}"
	And Robot Check "${eng_name}" Row Value Column of Child Class Table value is "${eng_value}"
	And Robot Check "${eng_name}" Row Highlight Column of Child Class Table value is "${hl}"
	And Robot Check "${eng_name}" Row Listable Column of Child Class Table value is "${listable}"
	And Robot Check "${eng_name}" Row Searchable Column of Child Class Table value is "${searchable}"
	And Robot Check "${eng_name}" Row Priority Column of Child Class Table value is "${prio}"
	#Sleep	2s
	
User Edit Child classification In "${hotel_name}" Hotel's "${tab}" Category; Name is: "${name}", Searchable: "${searchable}", Name Icon: "${name_icon}", Value: "${value}", Value Icon: "${value_icon}", Description: "${desc}", Charge: "${charge}", Charge Icon: "${charge_icon}", Highlighted: "${hl}", Listable: "${listable}", Priority: "${prio}"
	${eng_name}=		Robot Gets English Language from separated list		${name}
	${eng_value}=		Robot Gets English Language from separated list		${value}
	${eng_desc}=		Robot Gets English Language from separated list		${desc}
	${eng_charge}=		Robot Gets English Language from separated list		${charge}
	Given Navigate to Properties "${hotel_name}" Hotel on First start only
	And User Click "${tab}" Property Inner Tab
	And User Click "${eng_name}" row "Edit" Button
	When User Set New Child Class Name: "${name}"
	And User Set New Child Class Searchable: "${searchable}"
	And User Set Child Class Name Icon: "${name_icon}"
	And User Set New Child Class Value: "${value}"
	And User Set Child Class Value Icon: "${value_icon}"
	#And User Set Child Class Description: "${desc}"
	And User Set Child Class Description to TinyMCE: "${desc}"
	And User Set New Child Class Charge: "${charge}"
	And User Set Child Class Charge Icon: "${charge_icon}"
	And User Set Child Class Highlight: "${hl}"
	And User Set Child Class Listable: "${listable}"
	And User Set Child Class Priority: "${prio}"
	And User Click Child Class Save Button
	
	${alert}=	Run Keyword and Return Status
	...			Robot Should Get a Waited Modal Alert with This Message: "${ALERT_TAX_EXIST}" and Press "OK"
	${alert_prop}=	Run Keyword and Return Status
	...			Robot Should Get a Waited Modal Alert with This Message: "${ALERT_PROP_EXIST}" and Press "OK"
	#Run Keyword If 			'${alert}' == 'True'	User Click Property Clear Button
	Return From Keyword If	'${alert}' == 'True' or '${alert_prop}' == 'True'
	
	Then Robot Check "${eng_name}" Row Name Column of Child Class Table value is "${eng_name}"
	And Robot Check "${eng_name}" Row Value Column of Child Class Table value is "${eng_value}"
	And Robot Check "${eng_name}" Row Highlight Column of Child Class Table value is "${hl}"
	And Robot Check "${eng_name}" Row Listable Column of Child Class Table value is "${listable}"
	And Robot Check "${eng_name}" Row Searchable Column of Child Class Table value is "${searchable}"
	And Robot Check "${eng_name}" Row Priority Column of Child Class Table value is "${prio}"
	
User Delete Child classification from "${hotel_name}" Hotel's "${tab}" Category; Name is: "${name}"
	${eng}=		Robot Gets English Language from separated list		${name}
	Given Navigate to Properties "${hotel_name}" Hotel on First start only
	And User Click "${tab}" Property Inner Tab
	And User Click "${eng}" row "Delete" Button
	Then Robot Can't find "${eng}" Named row in Child Class table

#CHILD META:
User Create Child meta In "${hotel_name}" Hotel's "${tab}" Category; Name is: "${name}", Name Icon: "${name_icon}", Value: "${value}", Description: "${desc}", Listable: "${listable}", Priority: "${prio}"
	${eng}=		Robot Gets English Language from separated list		${name}
	Given Navigate to Properties "${hotel_name}" Hotel on First start only
	And User Click "${tab}" Property Inner Tab
	And User Click "Add new" Link of "Child metas" Block
	When User Set New Child Meta Name: "${name}"
	And User Set Child Meta Name Icon: "${name_icon}"
	And User Set New Child Meta Value: "${value}"
	#And User Set Child Meta Description: "${desc}"
	And User Set Child Meta Description to TinyMCE: "${desc}"
	And User Set Child Meta Listable: "${listable}"
	And User Set Child Meta Priority: "${prio}"
	And User Click Child Meta Save Button
	${alert}=	Run Keyword and Return Status
	...			Robot Should Get a Waited Modal Alert with This Message: "${ALERT_TAX_EXIST}" and Press "OK"
	Run Keyword If 			'${alert}' == 'True'	User Click Property Clear Button
	Return From Keyword If	'${alert}' == 'True'
	
	Then Robot Check "${eng}" Row Name Column of Child Meta Table value is "${eng}"
	And Robot Check "${eng}" Row Value Column of Child Meta Table value is "${value}"
	And Robot Check "${eng}" Row Listable Column of Child Meta Table value is "${listable}"
	And Robot Check "${eng}" Row Priority Column of Child Meta Table value is "${prio}"
	
User Edit Child meta In "${hotel_name}" Hotel's "${tab}" Category; Name is: "${name}", Name Icon: "${name_icon}", Value: "${value}", Description: "${desc}", Listable: "${listable}", Priority: "${prio}"
	${eng}=		Robot Gets English Language from separated list		${name}
	Given Navigate to Properties "${hotel_name}" Hotel on First start only
	And User Click "${tab}" Property Inner Tab
	And User Click "${eng}" row "Edit" Button
	When User Set New Child Meta Name: "${name}"
	And User Set Child Meta Name Icon: "${name_icon}"
	And User Set New Child Meta Value: "${value}"
	#And User Set Child Meta Description: "${desc}"
	And User Set Child Meta Description to TinyMCE: "${desc}"
	And User Set Child Meta Listable: "${listable}"
	And User Set Child Meta Priority: "${prio}"
	And User Click Child Meta Save Button
	
	${alert}=	Run Keyword and Return Status
	...			Robot Should Get a Waited Modal Alert with This Message: "${ALERT_TAX_EXIST}" and Press "OK"
	Run Keyword If 			'${alert}' == 'True'	User Click Property Clear Button
	Return From Keyword If	'${alert}' == 'True'
	
	Then Robot Check "${eng}" Row Name Column of Child Meta Table value is "${eng}"
	And Robot Check "${eng}" Row Value Column of Child Meta Table value is "${value}"
	And Robot Check "${eng}" Row Listable Column of Child Meta Table value is "${listable}"
	And Robot Check "${eng}" Row Priority Column of Child Meta Table value is "${prio}"
	
User Delete Child meta from "${hotel_name}" Hotel's "${tab}" Category; Name is: "${name}"
	${eng}=		Robot Gets English Language from separated list		${name}
	Given Navigate to Properties "${hotel_name}" Hotel on First start only
	And User Click "${tab}" Property Inner Tab
	When User Click "${eng}" row "Delete" Button
	Then Robot Can't find "${eng}" Named row in Child Metas table

#AGE RANGES
User Create Age Range to "${hotel_name}" hotel with; Name: "${name}" From: "${from}" To: "${to}" Banned: "${banned}" Free: "${free}"
	When User Set Age Range Name: "${name}"
	And User Set Age Range From: "${from}"
	And User Set Age Range To: "${to}"
	And User Set Age Range Banned: "${banned}"
	And User Set Age Range Free: "${free}"
	And User Click Age Range Save Button
	
	${alert_over}=		Run Keyword and Return Status
	...			Robot Should Get a Waited Modal Alert with This Message: "${ALERT_AGE_OVERLAP}" and Press "OK"
	${alert_exist}=		Run Keyword and Return Status
	...			Robot Should Get a Waited Modal Alert with This Message: "${ALERT_AGE_EXIST}" and Press "OK"
	
	Return From Keyword If	'${alert_over}' == 'True' or '${alert_exist}' == 'True'
	Then Robot Check "${name}" Row From value is "${from}"
	Then Robot Check "${name}" Row To value is "${to}"
	Then Robot Check "${name}" Row Banned value is "${banned}"
	Then Robot Check "${name}" Row Free value is "${free}"
	
User Edit Age Range to "${hotel_name}" hotel with; Name: "${name}" From: "${from}" To: "${to}" Banned: "${banned}" Free: "${free}"
	Given User Click "${name}" row "Edit" Button
	And Wait Until Element Contains Attribute		${AGE_RANGES_NAME_IN_JQ}@ng-reflect-model	${name}
	When User Set Age Range Name: "${name}"
	And User Set Age Range From: "${from}"
	And User Set Age Range To: "${to}"
	And User Set Age Range Banned: "${banned}"
	And User Set Age Range Free: "${free}"
	And User Click Age Range Save Button
	
	${alert_over}=		Run Keyword and Return Status
	...			Robot Should Get a Waited Modal Alert with This Message: "${ALERT_AGE_OVERLAP}" and Press "OK"
	${alert_exist}=		Run Keyword and Return Status
	...			Robot Should Get a Waited Modal Alert with This Message: "${ALERT_AGE_EXIST}" and Press "OK"
	
	Return From Keyword If	'${alert_over}' == 'True' or '${alert_exist}' == 'True'
	
	Then Robot Check "${name}" Row From value is "${from}"
	Then Robot Check "${name}" Row To value is "${to}"
	Then Robot Check "${name}" Row Banned value is "${banned}"
	Then Robot Check "${name}" Row Free value is "${free}"
	
User Delete "${name}" Age Range from "${hotel_name}" hotel
	When User Click "${name}" row "Delete" Button
	
	${alert}=	Run Keyword and Return Status
	...			Robot Should Get a Waited Modal Alert with This Message: "${ALERT_AGE_ACTIVE}" and Press "OK"
	${alert_adult}=		Run Keyword and Return Status
	...			Robot Should Get a Waited Modal Alert with This Message: "${ALERT_AGE_ADULT}" and Press "OK"
	
	Return From Keyword If	'${alert}' == 'True' or '${alert_adult}' == 'True'
	
	Then Robot Can't find "${name}" Named row in table

#PERIODS
User Create "${category}" Period to "${hotel_name}" Hotel; Name: "${name}", From: "${from}", To: "${to}", Minimum nights: "${min}", Meal Plans: "${mp}"
	Given Navigate to Periods "${hotel_name}" Hotel on First start only
	User Set "${category}" Period "From Date": "${from}"
	User Set "${category}" Period "To Date": "${to}"
	User Set "${category}" Period "Name": "${name}"
	Run Keyword If	'${category}' == 'Open periods'		User Set "${category}" Period "Minimum nights": "${min}"
	Run Keyword If	'${category}' == 'Open periods'		User Set "${category}" Period "Meal Plans": "${mp}"
	User Press "${category}" "Save" Button
	
User Edit "${category}" Period in "${hotel_name}" Hotel; Name: "${name}", From: "${from}", To: "${to}", Minimum nights: "${min}", Meal Plans: "${mp}"
User Delete "${category}" Period from "${hotel_name}" Hotel; Name: "${name}"

#ROOMS
User Create Room to "${hotel_name}" hotel with; Name: "${name}" Amount: "${amount}"
	${eng}=		Robot Gets English Language from separated list		${name}
	Robot Helps Push The Button			${ROOMS_NEW_DEVICE}
	When User Set New Room Name: "${name}"
	And User Set Room Amount: "${amount}"
	And User Click Room Save Button
	
	${alert}=	Run Keyword and Return Status
	...			Robot Should Get a Waited Modal Alert with This Message: "${ALERT_ROOM_EXIST}" and Press "OK"			
	Return From Keyword If	'${alert}' == 'True'
	
	Then Robot Check "${eng}" Row Amount value is "${amount}"
	
User Edit Room to "${hotel_name}" hotel with; Name: "${name}" Amount: "${amount}"
	${eng}=		Robot Gets English Language from separated list		${name}
	Given User Click "${eng}" row "Edit" Button
	Wait Until Angular Ready
	When User Set Room Name: "${name}"
	And User Set Room Amount: "${amount}"
	And User Click Room Save Button
	
	${alert}=	Run Keyword and Return Status
	...			Robot Should Get a Waited Modal Alert with This Message: "${ALERT_ROOM_EXIST}" and Press "OK"			
	Return From Keyword If	'${alert}' == 'True'
	
	Then Robot Check "${eng}" Row Amount value is "${amount}"
	
User Delete "${name}" Room from "${hotel_name}" hotel
	${name}=		Robot Gets English Language from separated list		${name}
	${sel}=			Convert To String		jquery=tr:contains("${name}") a:contains("Delete")
	Wait Until Page Contains Element		${sel}
	Run Keyword And Ignore Error		Click Element	${sel}
	Alert Should Be Present			Are you sure to delete this device?
	Then Robot Can't find "${name}" Named row in table

#ROOMS/USAGE
User Create Room Usage with; Age group: "${age}" and Number: "${number}"
	User Click Add Usage Button
	
	${age_list}=		Split String		${age}		;
	${number_list}=		Split String		${number}	;
	
	${len}=				Get Length	${age_list}
	:FOR	${index}	IN RANGE	${len}
	\	User Set Room Usage Age Group: "${age_list[${index}]}"
	\	User Set Room Usage Age Number: "${number_list[${index}]}"
	\	User Click Room Usage Set Usage Button
	
	User Click Room Usage Save Button
	
User Edit Room Usage with; Age group: "${age}" and Number: "${number}" to: "${age_to}" and Number: "${number_to}"
	#User Click Add Usage Button
	
	#Robot Make Json from Semicolon Separated Datas
	${dict}=	Create Dictionary
	
	${age_list}=		Split String		${age}		;
	${number_list}=		Split String		${number}	;
	
	${len}=				Get Length	${age_list}
	:FOR	${index}	IN RANGE	${len}
	#\	${key_val}=			Convert To String	${age_list[${index}]}=${number_list[${index}]}
	\	${num}=		Convert To Integer	${number_list[${index}]}
	\	Set To Dictionary	${dict}		${age_list[${index}]}	${num}
	
	${json}=			json.dumps	${dict}
	${json_as_str}=		Convert To String	${json}
	${json_strip}=		Remove String	${json_as_str}	${SPACE}
	
	${sel}=			Convert To String		jquery=tr:contains(${json_strip}) a:contains("Edit")
	Wait Until Page Contains Element		${sel}
	Click Element	${sel}
	
	${age_to_list}=		Split String		${age_to}		;
	${number_to_list}=		Split String		${number_to}	;
	
	${len_to}=				Get Length	${age_to_list}
	:FOR	${index}	IN RANGE	${len_to}
	\	User Set Room Usage Age Group: "${age_to_list[${index}]}"
	\	User Set Room Usage Age Number: "${number_to_list[${index}]}"
	\	User Click Room Usage Set Usage Button
	
	User Click Room Usage Save Button
	
User Delete "${name}" Room Usage where: Age group: "${age}" and Number: "${number}"
	${dict}=	Create Dictionary
	
	${age_list}=		Split String		${age}		;
	${number_list}=		Split String		${number}	;
	
	${len}=				Get Length	${age_list}
	:FOR	${index}	IN RANGE	${len}
	\	${num}=		Convert To Integer	${number_list[${index}]}
	\	Set To Dictionary	${dict}		${age_list[${index}]}	${num}
	
	${json}=			json.dumps	${dict}
	${json_as_str}=		Convert To String	${json}
	${json_strip}=		Remove String	${json_as_str}	${SPACE}
	
	${sel}=			Convert To String		jquery=tr:contains(${json_strip}) a:contains("Delete")
	Wait Until Page Contains Element		${sel}
	Click Element	${sel}

#AVAILABILITIES
User Change Availability "${av_stat}" to "${av_room}" Room "${av_year}" Year and "${av_month}" Month and "${av_days}" Days
	Wait Until Angular Ready
	
	${sel_room}=		Convert To String		jquery=select option:contains("${av_room}")
	Wait Until Page Contains Element	${sel_room}
	Click Element		${sel_room}
	${sel_year}=		Convert To String		jquery=table select option:contains("${av_year}")
	Click Element		${sel_year}
	Sleep		1s
	
	@{list}=			Split String		${av_days}	,
	:FOR    ${ELEMENT}    IN    @{list}
    \	${sel_d}=			Convert To String	xpath=//th[contains(text(), '${av_month}')]/..//strong[. = '${ELEMENT}']
    \	Click Element		${sel_d}
	
	Sleep		1s
	Run Keyword If 		'${av_stat}' == 'Available'			Click Element		${AVLBLTS_S_AVAILABLE_BTN}
	...		ELSE IF		'${av_stat}' == 'Not Available'		Click Element		${AVLBLTS_S_NOT_AVAILABLE_BTN}
	...		ELSE IF		'${av_stat}' == 'Clear'				Click Element		${AVLBLTS_S_CLEAR_SEL_BTN}


#PRICES
User Edit Main Margin Value of "${hotel_name}" Hotel with "${margin_key}" Key: "${margin_value}" Value
	${value_sel}=		Convert To String		jquery=th:contains("${margin_key}") input
	#Wait Until Page Contains Element		${PRICES_S_EDIT_MODE} input
	Robot Helps Push The Button				${PRICES_S_EDIT_MODE}
	#Select Checkbox		${PRICES_S_EDIT_MODE} input
	Wait Until Page Contains Element		${value_sel}
	Wait Until Element Contains Attribute	${value_sel}@ng-reflect-is-disabled		false
	Input Text			${value_sel}	${margin_value}
	#Press tab:
	#Press Key			${value_sel}	\\09
	Robot Helps Push The Button		${PRICES_S_SAVE_BTN}

User Edit Price table of "${hotel_name}" Hotel of "${room_name}" room and "${row_name}" row and "${date_period}" date period and "${meal_plan}" meal plan to "${input_type}": "${value}" value
	${devices_dict}=	Robot Helps to Create Device Dictionary
	${room_sel}=		Convert To String		jquery=tbody:contains("${room_name}") th:contains("${row_name}"):eq(1)
	#Log					Room Selector: ${room_sel}	WARN
	${room_id}=			Robot Get Element Id Number		${room_sel}		1
	#Log					Room Id: ${room_id}			WARN
	${date_p_sel}=		Convert To String		jquery=th:contains("${date_period}")
	${date_p_id}=		Robot Get Element Id Number		${date_p_sel}	1
	#Log					Period Id: ${date_p_id}		WARN
	
	Robot Helps Push The Button		${PRICES_S_EDIT_MODE}
	${id}=				Catenate	SEPARATOR=_		${input_type}	${room_id}	${date_p_id}	${meal_plan}
	#Log 		id: ${id}	WARN
	Wait Until Page Contains Element		id=${id}
	Wait Until Element Contains Attribute	id=${id}@ng-reflect-is-disabled		false
	Input Text			id=${id}	${value}
	Robot Helps Push The Button		${PRICES_S_SAVE_BTN}
	
	
User Add Price Row to "${hotel_name}" Hotel, "${room_name}" Room with Name: "${row_name}", Age Range: "${age}", Amount: "${amount}" and Extra is "${extra}"
	${eng}=		Robot Gets English Language from separated list		${row_name}	
	Given User Click Add New Price Row Button to "${room_name}" Room
	Then The Prices Page contains the Price row editor modal
	When User Set New Price Row Name: "${row_name}"
	And User Set New Price Row Age Range: "${age}"
	And User Set New Price Row Amount: "${amount}"
	And User Set New Price Row Extra: "${extra}"
	And User Click Save New Price Button
	
	${alert}=	Run Keyword and Return Status
	...			Robot Should Get a Waited Modal Alert with This Message: "${ALERT_PRICE_EXIST}" and Press "OK"			
	Return From Keyword If	'${alert}' == 'True'
	
	#Then The Prices Page does not contains the Price row editor modal
	And Robot Check "${eng}" Price Row is exists in "${room_name}" Named Room
	
User Edit Price Row in "${hotel_name}" Hotel, "${room_name}" Room Name: "${row_name}", Age Range: "${age}", Amount: "${amount}" and Extra is "${extra}"
	${eng}=		Robot Gets English Language from separated list		${row_name}	
	#Given User Click Add New Price Row Button to "${room_name}" Room
	Given User Click "Edit" Button in "${eng}" Named Price Row in "${room_name}" Room
	Then The Prices Page contains the Price row editor modal
	When User Set New Price Row Name: "${row_name}"
	And User Set New Price Row Age Range: "${age}"
	And User Set New Price Row Amount: "${amount}"
	And User Set New Price Row Extra: "${extra}"
	And User Click Save New Price Button
	
	${alert}=	Run Keyword and Return Status
	...			Robot Should Get a Waited Modal Alert with This Message: "${ALERT_PRICE_EXIST}" and Press "OK"			
	Return From Keyword If	'${alert}' == 'True'
	
	#Then The Prices Page does not contains the Price row editor modal
	And Robot Check "${eng}" Price Row is exists in "${room_name}" Named Room

User Delete "${row_name}" Named Price Row from "${room_name}" Named Room Name
	${eng}=		Robot Gets English Language from separated list		${row_name}
	#${sel}=			Convert To String	jquery=tbody:contains("${room_name}") th:contains("${eng}") button:contains("Delete")
	${btn_glyphicon}	Get From Dictionary		${BUTTONS_WITH_GLYPHICON}	Delete
	${sel}=				Convert To String	jquery=tbody:contains("${room_name}") th:contains("${eng}") button:has(${btn_glyphicon})
	#Wait Until Angular Ready
	#Wait Until Page Contains Element	${sel}
	Robot Helps Push The Button 		${sel}
	#Run Keyword And Ignore Error	Click Element	${sel}
	#Alert Should Be Present			Are you sure to delete this price?
	Robot Should Get a Waited Modal Alert with This Message: "Are you sure to delete this price row?" and Press "Yes"
	Robot Can't find "${eng}" Named row in "${room_name}" Named Room
	
#DISCOUNTS


#DISCOUNT COMBINATIONS
User Edit "${row_name}" Discount Combination Row; Select Checkboxes: "${columns}" and drag to "${dd}"
	#${tr}=	Convert To String	jquery=tr th:contains("${row_name}"):eq(1)
	${tr}=	Convert To String	jquery=tr:contains("${row_name}"):eq(1)
	
	User Check "${columns}" Checkboxes in Discount Combination Table Row "${row_name}"
	#Run Keyword If	${dd} > 0	Robot Helps in Drag and Drop to Numbered Position	${tr}	${dd}

#CONTENTS


User Create Content with; Title: "${title}" Lead: "${lead}" Content: "${content}" Meta Description: "${meta_description}" Meta Keyword: "${meta_keyword}" Meta Title: "${meta_title}" Status: "${status}"
	Wait Until Angular Ready
	User Click Add New Content Button
	Wait Until Page Contains Element	${ADD_NEW_CONTENT_MODAL}
	Wait Until Page Contains Element	${ADD_NEW_CONTENT_TITLE}
	Robot Helps Select an Option with Jquery	${ADD_NEW_CONTENT_STATUS_SELECT}	${status}
	Input Text							${ADD_NEW_CONTENT_TITLE}	                ${title}
	Input Text							${ADD_NEW_CONTENT_LEAD}	                    ${lead}
    Input Text							${ADD_NEW_CONTENT_META_DESCRIPTION}	        ${meta_description}
    Input Text							${ADD_NEW_CONTENT_META_KEYWORD} 	        ${meta_keyword}
    Input Text							${ADD_NEW_CONTENT_META_TITLE}	            ${meta_title}
	Click Element						${ADD_NEW_CONTENT_SAVE}
	Element Should Not Be Visible       ${UITOOL_MODAL}
	Element Should Not Contain          ${BODY_JQ}                                  Content page with same name exists
	Element Should Contain		        ${CONTENT_LIST_TABLE}	                    ${title}
#
#	${alert}=	Run Keyword and Return Status
#	...			Robot Should Get a Waited Modal Alert with This Message: "${ALERT_ORG_EXIST}" and Press "OK"
#	Return From Keyword If	'${alert}' == 'True'
#
#	Wait Until Element Contains			${ORG_MAIN_TABLE}			${name}

User Edit Content with; Title: "${title}" Lead: "${lead}" Content: "${content}" Meta Description: "${meta_description}" Meta Keyword: "${meta_keyword}" Meta Title: "${meta_title}" Status: "${status}"
    Wait Until Angular Ready
    ${editButton}=	Convert To String	jquery=tr:contains("${title}") button.btn-warning
    Click Element   ${editButton}
    Wait Until Page Contains Element	${ADD_NEW_CONTENT_MODAL}
    Wait Until Page Contains Element	${ADD_NEW_CONTENT_TITLE}
    Robot Helps Select an Option with Jquery	${ADD_NEW_CONTENT_STATUS_SELECT}	${status}
    Input Text							${ADD_NEW_CONTENT_TITLE}	                ${title}
    Input Text							${ADD_NEW_CONTENT_LEAD}	                    ${lead}
    Input Text							${ADD_NEW_CONTENT_META_DESCRIPTION}	        ${meta_description}
    Input Text							${ADD_NEW_CONTENT_META_KEYWORD} 	        ${meta_keyword}
    Input Text							${ADD_NEW_CONTENT_META_TITLE}	            ${meta_title}
    Click Element						${ADD_NEW_CONTENT_SAVE}
    Element Should Not Be Visible       ${UITOOL_MODAL}
    Element Should Not Contain          ${BODY_JQ}                                  Content page with same name exists
    Element Should Contain		        ${CONTENT_LIST_TABLE}	                    ${title}

User Create Content Category with; English: "${en}" German: "${de}" Hungarian: "${hu}" Russiona: "${ru}"
    Wait Until Angular Ready
	User Click Add New Content Category Button
	Wait Until Page Contains Element	${ADD_NEW_CONTENT_CATEGORY_MODAL}
    Input Text							${ADD_NEW_CONTENT_CATEGORY_ENGLISH}         ${en}
    Input Text							${ADD_NEW_CONTENT_CATEGORY_GERMAN}          ${de}
    Input Text							${ADD_NEW_CONTENT_CATEGORY_HUNGARIAN}       ${hu}
    Input Text							${ADD_NEW_CONTENT_CATEGORY_RUSSIAN}         ${ru}
    Click Element						${ADD_NEW_CONTENT_CATEGORY_SAVE}
    Element Should Not Be Visible       ${UITOOL_MODAL}
    Element Should Not Contain          ${BODY_JQ}                                  Content page with same name exists
    Element Should Contain		        ${CONTENT_CATEGORY_LIST_TABLE}	            ${en}

User Edit Content Category with; English: "${en}" German: "${de}" Hungarian: "${hu}" Russiona: "${ru}"
    Wait Until Angular Ready
	${editButton}=	Convert To String	jquery=tr:contains("${en}") button.btn-warning
	Click Element   ${editButton}
	Wait Until Page Contains Element	${ADD_NEW_CONTENT_CATEGORY_MODAL}
    Input Text							${ADD_NEW_CONTENT_CATEGORY_ENGLISH}         ${en}
    Input Text							${ADD_NEW_CONTENT_CATEGORY_GERMAN}          ${de}
    Input Text							${ADD_NEW_CONTENT_CATEGORY_HUNGARIAN}       ${hu}
    Input Text							${ADD_NEW_CONTENT_CATEGORY_RUSSIAN}         ${ru}
    Click Element						${ADD_NEW_CONTENT_CATEGORY_SAVE}
    Element Should Not Be Visible       ${UITOOL_MODAL}
    Element Should Not Contain          ${BODY_JQ}                                  Content page with same name exists
    Element Should Contain		        ${CONTENT_CATEGORY_LIST_TABLE}	            ${en}
	
#GALLERY:
#User Upload "${path}" Image(s)
User Upload Image(s)
	[Arguments]		${path}
	#Log		${path}		WARN
	User Open Gallery Upload Modal
	User Choose This File: "${path}"
	#User Set Description To Image: "${description}"
	User Click Upload All Button
	Robot Wait Standalone Attribute Disappeared from Selector	
	...									${GALLERY_UPLOAD_DONE_BTN}	disabled
	[Teardown]	User Click Done Button Gallery Upload Modal
	
User Edit Image: "${image}"; Description: "${desc}"; Highlighted: "${hl}"
	${eng}=		Robot Gets English Language from separated list		${desc}
	${row}=		User Select Image: "${image}"
	Log 	${row}	WARN
	User Set Description To Image: "${desc}"
	User Set Image Highlight: "${hl}"
	Robot Wait Standalone Attribute Disappeared from Selector	
	...									${GALLERY_UPLOAD_DONE_BTN}	disabled
	User Click Set Button Gallery Edit Modal
	#Robot Check The Row Is Exist	${eng}
	Robot Waits Text Visible in Selector	${eng}	${row}
User Delete "${image}" Image
	User Delete Image: "${image}"
	Robot Should Get a Waited Modal Alert with This Message: "${ALERT_DELETE_IMAGE}" and Press "Yes"
User Set Gallery Properties; Name: "${name}", Role: "${role}"
	User Set Gallery Name: "${name}"
	User Set Gallery Role: "${role}"
	Robot Helps Push The Button		${ADD_NEW_CONTENT_SAVE}