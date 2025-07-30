*** Variables ***
#NAVIGATION:
#MAIN:
${MAIN_NAVIGATION_CONTENTS}      jquery=nav.navbar ul.navbar-nav li a:contains("Contents")

#TABS:
${ORG_NAV_GENERAL_TAB}			jquery=ul li a:contains("Main")
${ORG_NAV_LOCATION_TAB}			jquery=ul li a:contains("Location")
${ORG_NAV_PROPERTIES_TAB}		jquery=ul li a:contains("Properties")
${ORG_NAV_AGE_RANGES_TAB}		jquery=ul li a:contains("Age Ranges")
${ORG_NAV_PERIODS_TAB}			jquery=ul li a:contains("Periods")
${ORG_NAV_ROOMS_TAB}			jquery=ul li a:contains("Rooms")
${ORG_NAV_AVAILABILITIES_TAB}	jquery=ul li a:contains("Availabilities")
${ORG_NAV_PRICES_TAB}			jquery=ul li a:contains("Prices")
${ORG_NAV_DISCOUNTS_TAB}		jquery=ul li a:contains("Price Modifiers")
${ORG_NAV_DISCOUNT_COMBOS}		jquery=ul li a:contains("Discount Combinations")
${ORG_NAV_GALLERIES}			jquery=ul li a:contains("Galleries")
${ORG_NAV_BREADCRUMB}			xpath=/html//organization-navigation-component/breadcrumb-component/ul

${BREADCRUMB_DEVICE}			jquery=breadcrumb-component ul li:eq(1) a

#DOM:
#MAIN PAGE:
${ORG_MAIN_ADD_NEW}				css=body > app-component > div > ng-component > div > button
${ORG_MAIN_NEW_HOTEL_DIALOG}	jquery=new-accommodation-component
${ORG_MAIN_NH_NAME_INPUT}		${ORG_MAIN_NEW_HOTEL_DIALOG} input[formcontrolname=name]
${ORG_MAIN_NEW_HOTEL_CATEGORY_SELECT}	jquery=new-accommodation-component select[ng-reflect-name="Accommodation Category"]
${ORG_MAIN_NEW_HOTEL_DISCOUNT_SELECT}	jquery=new-accommodation-component select[ng-reflect-name="Discount calculations base"]
${ORG_MAIN_NEW_HOTEL_MFN_SELECT}	jquery=new-accommodation-component select[ng-reflect-name="Merged free nights"]
${ORG_MAIN_NH_NAME_SAVE}		${ORG_MAIN_NEW_HOTEL_DIALOG} > div > div > form > div.modal-footer > button.btn.btn-primary.btn-sm
${ORG_MAIN_NH_NAME_CANCEL}		${ORG_MAIN_NEW_HOTEL_DIALOG} > div > div > form > div.modal-footer > button.btn.btn-default.btn-sm
${ORG_MAIN_TABLE}				css=body > app-component > div > ng-component > div > table


#GENERAL:
${GENERAL_ACTIVE_CHK_JQ}		jquery=label:contains("Active") input
${GENERAL_PARENT_SEL_JQ}		jquery=select[ng-reflect-name="parent"]
${GENERAL_ENG_NAME_JQ}			jquery=label:contains("Name") tr:contains("English") input
${GENERAL_ENG_SH_DES_JQ}		jquery=label:contains("Short description") tr:contains("English") input
${GENERAL_ENG_LO_DES_JQ}		jquery=label:contains("Long description") tr:contains("English") textarea
${GENERAL_ENG_LO_DES_TMCE_JQ}	jquery=label:contains("Long description") tr:contains("English") iframe
${GENERAL_SAVE_JQ}				jquery=button:contains("Save")
${GENERAL_CLEAR_JQ}				jquery=button:contains("Clear")

#LOCATION:
${LOCATION_ISLAND_SELECT_JQ}	jquery=label:contains("Island") select
${LOCATION_DISCRICT_SELECT_JQ}	jquery=label:contains("District") select
${LOCATION_LATITUDE_JQ}			jquery=label:contains("Latitude") input
${LOCATION_LONGITUDE_JQ}		jquery=label:contains("Longitude") input
${LOCATION_PO_BOX_JQ}			jquery=label:contains("P.O. Box") input
${LOCATION_SAVE_BTN_JQ}			jquery=button:contains("Save")

#PROPERTIES:
#Tabs:
${PROP_TABS}			jquery=.nav-tabs:eq(1) li
${PROP_TABS_BY_NAME}	Facilities;Amenities;Check In/Out;Policies;General;Contact;Settings

${PROPERTIES_NAME_TABLE_JQ}		jquery=label:contains("Name") table
${PROPERTIES_NAME_INPUT_JQ}		jquery=label:contains("Name") input
${PROPERTIES_NAME_SET_JQ}		jquery=label:contains("Name") button:contains("Set")
${PROPERTIES_NAME_ENG_IN_JQ}	jquery=label:contains("Name") table tr:contains("English") select
${PROPERTIES_NAME_ENG_ADD_JQ}	jquery=label:contains("Name") table tr:contains("English") button
${PROPERTIES_CHARGE_TABLE_JQ}	jquery=label:contains("Charge") table
${PROPERTIES_CHARGE_INPUT_JQ}	jquery=label:contains("Charge") input
${PROPERTIES_CHARGE_SET_JQ}		jquery=label:contains("Charge") button:contains("Set")
${PROPERTIES_CHARGE_ENG_IN_JQ}	jquery=label:contains("Charge") table tr:contains("English") select
${PROPERTIES_CHARGE_ENG_ADD_JQ}	jquery=label:contains("Charge") table tr:contains("English") button
${PROPERTIES_HIGHLIGHT_SEL_JQ}	jquery=label:contains("Highlighted") input
${PROPERTIES_LISTABLE_SEL_JQ}	jquery=label:contains("Listable") input
${PROPERTIES_PRIORITY_IN_JQ}	jquery=label:contains("Priority") input
${PROPERTIES_SAVE_BTN_JQ}		jquery=form button:contains("Save")
${PROPERTIES_CLEAR_BTN_JQ}		jquery=form button:contains("Clear")
${PROPERTIES_TABLE_XP}			xpath=//div/table-container/table
${PROPERTIES_CH_CLASS_TAB_JQ}	jquery=label:contains("Child classifications") table
${PROPERTIES_CH_CLASS_ADD_JQ}	jquery=label:contains("Child classifications") a:contains("Add new")
${PROPERTIES_CH_META_TAB_JQ}	jquery=label:contains("Child metas") table
${PROPERTIES_CH_META_ADD_JQ}	jquery=label:contains("Child metas") a:contains("Add new")

${CLASS_ED_DIV}					css=#classification-modal-component
${CLASS_ED_NAME_TABLE_JQ}		jquery=.modal-content label:contains("Name") table
${CLASS_ED_NAME_INPUT_JQ}		jquery=.modal-content label:contains("Name") input:eq(1)
${CLASS_ED_NAME_SET_JQ}			jquery=.modal-content label:contains("Name") button:contains("Set")
${CLASS_ED_SEARCHABLE_JQ}		jquery=.modal-content tr label:contains("Searchable") input
${CLASS_ED_NAME_ICON_SEL}		jquery=.modal-content label:contains("Name") tr:contains("Icon (all languages)") select
${CLASS_ED_NAME_ENG_IN_JQ}		jquery=.modal-content label:contains("Name") table tr:contains("English") select
${CLASS_ED_NAME_ENG_ADD_JQ}		jquery=.modal-content label:contains("Name") table tr:contains("English") button
${CLASS_ED_VALUE_TABLE_JQ}		jquery=.modal-content label:contains("Value") table
${CLASS_ED_VALUE_INPUT_JQ}		jquery=.modal-content label:contains("Value") input:eq(1)
${CLASS_ED_VALUE_SET_JQ}		jquery=.modal-content label:contains("Value") button:contains("Set")
${CLASS_ED_VALUE_ENG_IN_JQ}		jquery=.modal-content label:contains("Value") table tr:contains("English") select
${CLASS_ED_VALUE_ENG_ADD_JQ}	jquery=.modal-content label:contains("Value") table tr:contains("English") button
${CLASS_ED_VALUE_ICON_SEL}		jquery=.modal-content label:contains("Value") tr:contains("Icon (all languages)") select
${CLASS_ED_CHARGE_TABLE_JQ}		jquery=.modal-content label:contains("Charge") table
${CLASS_ED_CHARGE_INPUT_JQ}		jquery=.modal-content label:contains("Charge") input
${CLASS_ED_CHARGE_SET_JQ}		jquery=.modal-content label:contains("Charge") button:contains("Set")
${CLASS_ED_CHARGE_ENG_IN_JQ}	jquery=.modal-content label:contains("Charge") table tr:contains("English") select
${CLASS_ED_CHARGE_ENG_ADD_JQ}	jquery=.modal-content label:contains("Charge") table tr:contains("English") button
${CLASS_ED_CHARGE_ICON_SEL}		jquery=.modal-content label:contains("Charge") tr:contains("Icon (all languages)") select
${CLASS_ED_DESC_TABLE_JQ}		jquery=.modal-content label:contains("Description") table
${CLASS_ED_DESC_ENG_IN_JQ}		jquery=.modal-content label:contains("Description") table tr:contains("English") textarea
${CLASS_ED_DESC_ENG_TMCE_JQ}	jquery=.modal-content label:contains("Description") table tr:contains("English") iframe
${CLASS_ED_HIGHLIGHT_SEL_JQ}	jquery=.modal-content label:contains("Highlighted") input
${CLASS_ED_LISTABLE_SEL_JQ}		jquery=.modal-content label:contains("Listable") input
${CLASS_ED_PRIORITY_IN_JQ}		jquery=.modal-content label:contains("Priority") input
${CLASS_ED_SAVE_BTN_JQ}			jquery=.modal-content form button:contains("Set")
${CLASS_ED_CLEAR_BTN_JQ}		jquery=.modal-content form button:contains("Cancel")

${META_ED_DIV}					css=#meta-modal-component
${META_ED_NAME_TABLE_JQ}		${CLASS_ED_NAME_TABLE_JQ}
${META_ED_NAME_INPUT_JQ}		${CLASS_ED_NAME_INPUT_JQ}
${META_ED_NAME_SET_JQ}			${CLASS_ED_NAME_SET_JQ}
${META_ED_NAME_ENG_IN_JQ}		${CLASS_ED_NAME_ENG_IN_JQ}
${META_ED_NAME_ENG_ADD_JQ}		${CLASS_ED_NAME_ENG_ADD_JQ}
${META_ED_VALUE_INPUT_JQ}		jquery=.modal-content label:contains("Value") input
${META_ED_DESC_TABLE_JQ}		${CLASS_ED_DESC_TABLE_JQ}
${META_ED_DESC_ENG_IN_JQ}		${CLASS_ED_DESC_ENG_IN_JQ}
${META_ED_DESC_ENG_TMCE_JQ}		${CLASS_ED_DESC_ENG_TMCE_JQ}
${META_ED_LISTABLE_SEL_JQ}		${CLASS_ED_LISTABLE_SEL_JQ}
${META_ED_PRIORITY_IN_JQ}		${CLASS_ED_PRIORITY_IN_JQ}
${META_ED_SAVE_BTN_JQ}			${CLASS_ED_SAVE_BTN_JQ}
${META_ED_CLEAR_BTN_JQ}			${CLASS_ED_CLEAR_BTN_JQ}


#AGE RANGES:
#${AGE_RANGES_NAME_IN_JQ}		jquery=label:contains("Name") input
${AGE_RANGES_NAME_IN_JQ}		jquery=label:contains("Name") select
${AGE_RANGES_FROM_IN_JQ}		jquery=label:contains("From Age") input
${AGE_RANGES_TO_IN_JQ}			jquery=label:contains("To Age") input
${AGE_RANGES_BANNED_IN_JQ}		jquery=label:contains("Banned") input
${AGE_RANGES_FREE_IN_JQ}		jquery=label:contains("Free") input
${AGE_RANGES_SAVE_BTN_JQ}		jquery=button:contains("Save")
${AGE_RANGES_CLEAR_BTN_JQ}		jquery=button:contains("Clear")

${EQ_OPEN_PERIOD}				0
${EQ_DISCOUNT_PERIOD}			1
${EQ_CLOSURE_PERIOD}			2

${PERIODS_OPEN_FORM_JQ}			jquery=form:eq(${EQ_OPEN_PERIOD})
${PERIODS_DISC_FORM_JQ}			jquery=form:eq(${EQ_DISCOUNT_PERIOD})
${PERIODS_CLOSURE_FORM_JQ}		jquery=form:eq(${EQ_CLOSURE_PERIOD})

#PERIODS-CLOSURES:
${PERIODS_S_CLOS_FROM_INPUT}	jquery=fieldset:contains("From Date"):eq(${EQ_CLOSURE_PERIOD}) input
${PERIODS_S_CLOS_TO_INPUT}		jquery=fieldset:contains("To Date"):eq(${EQ_CLOSURE_PERIOD}) input
${PERIODS_S_CLOS_NAME_INPUT}	jquery=label:contains("Name"):eq(${EQ_CLOSURE_PERIOD}) input
${PERIODS_S_CLOS_SAVE_BTN}		jquery=button:contains("Save"):eq(${EQ_CLOSURE_PERIOD})
${PERIODS_S_CLOS_TABLE_R}		xpath=/html/body/app-component/div/ng-component/div/ng-component/div/div[1]/div[2]/table-container/table//tr

#PERIODS-OPEN_PERIODS:
${PERIODS_S_OP_FROM_INPUT}		jquery=label:contains("From Date"):eq(${EQ_OPEN_PERIOD}) input
${PERIODS_S_OP_TO_INPUT}		jquery=label:contains("To Date"):eq(${EQ_OPEN_PERIOD}) input
${PERIODS_S_OP_NAME_INPUT}		jquery=label:contains("Name"):eq(${EQ_OPEN_PERIOD}) input
${PERIODS_S_OP_MN_INPUT}		jquery=label:contains("Minimum nights"):eq(0) input
${PERIODS_S_OP_SAVE_BTN}		jquery=button:contains("Save"):eq(${EQ_OPEN_PERIOD})
${PERIODS_S_OP_TABLE_R}			xpath=/html/body/app-component/div/ng-component/div/ng-component/div/div[2]/div[2]/table-container/table//tr
${PERIODS_S_OP_MP_EP_CHK}		jquery=label:contains("Meal Plans") option:contains("e/p")
${PERIODS_S_OP_MP_BB_CHK}		jquery=label:contains("Meal Plans") option:contains("b/b")
${PERIODS_S_OP_MP_HB_CHK}		jquery=label:contains("Meal Plans") option:contains("h/b")
${PERIODS_S_OP_MP_FB_CHK}		jquery=label:contains("Meal Plans") option:contains("f/b")
${PERIODS_S_OP_MP_IN_CHK}		jquery=label:contains("Meal Plans") option:contains("inc")

#PERIODS-DISCOUNT_PERIODS:
${PERIODS_S_DISC_FROM_INPUT}	jquery=label:contains("From Date"):eq(${EQ_DISCOUNT_PERIOD}) input
${PERIODS_S_DISC_TO_INPUT}		jquery=label:contains("To Date"):eq(${EQ_DISCOUNT_PERIOD}) input
${PERIODS_S_DISC_NAME_INPUT}	jquery=label:contains("Name"):eq(${EQ_DISCOUNT_PERIOD}) input
${PERIODS_S_DISC_SAVE_BTN}		jquery=button:contains("Save"):eq(${EQ_DISCOUNT_PERIOD})

#ROOMS:
${ROOMS_FIRST_ROOM}						jquery=td:eq(1)
${ROOMS_NEW_DEVICE}						jquery=button:contains("Add a new device")
${ROOMS_NAME_ENG_JQ}					jquery=label:contains("Name") tr:contains("English") input
${ROOMS_NAME_JQ}						jquery=label:contains("Name") input
${ROOMS_AMOUNT_JQ}						jquery=label:contains("Amount") input
${ROOMS_ADD_USAGE_JQ}					jquery=label:contains("Maximum usages") a:contains("Add usage")
${ROOMS_USAGE_ED_AGE_JQ}				jquery=.modal-dialog label:contains("Age group") select
${ROOMS_USAGE_ED_NUMBER_JQ}				jquery=.modal-dialog label:contains("Number") input
${ROOMS_USAGE_ED_SET_USAGE_BTN_JQ}		jquery=.modal-dialog form button:contains("Set")
${ROOMS_USAGE_ED_CLEAR_USAGE_BTN_JQ}	jquery=.modal-dialog form button:contains("Clear")
${ROOMS_USAGE_ED_SAVE_BTN_JQ}			jquery=.modal-footer button:contains("Set")
${ROOMS_USAGE_ED_CLEAR_BTN_JQ}			jquery=.modal-footer button:contains("Cancel")
${ROOMS_SAVE_BTN_JQ}					jquery=button:contains("Save")
${ROOMS_CLEAR_BTN_JQ}					jquery=button:contains("Clear"):visible

#ROOM MIN. NIGHTS
${ROOM_MIN_N_DATES}						jquery=thead th div.small
${ROOM_MIN_N_COMMON_VALUE_INPUT}		jquery=.panel input
${ROOM_MIN_N_SET_BTN}					jquery=.panel button:contains("Set")
${ROOM_MIN_N_ENABLE_BTN}				jquery=.panel button:contains("Enable")
${ROOM_MIN_N_DISABLE_BTN}				jquery=.panel button:contains("Disable")
${ROOM_MIN_N_SAVE_BTN}					jquery=a:contains("Save changes")

#AVAILABILITIES:
${AVLBLTS_S_ROOM_SELECT}		jquery=select
${AVLBLTS_S_AVAILABLE_BTN}		jquery=.btn-success
${AVLBLTS_S_NOT_AVAILABLE_BTN}	jquery=.btn-danger
${AVLBLTS_S_CLEAR_SEL_BTN}		jquery=.btn-warning
${AVLBLTS_S_YEAR_SEL}			css=#year
${AVLBLTS_S_AVLBLT_TABLE}		jquery=table

#PRICES:
${PRICES_S_NET_PRICES_CHK}		jquery=label:contains("Net prices")
${PRICES_S_MARGINS_CHK}			jquery=label:contains("Margins")
${PRICES_S_RACK_PRICES_CHK}		jquery=label:contains("Rack prices")
${PRICES_S_EDIT_MODE}			jquery=button:contains("Edit prices")
${PRICES_S_TABLE_R}				xpath=/html/body/app-component/div/ng-component/div/ng-component/div/price-grid-component/table//tr
${PRICES_S_SAVE_BTN}			jquery=button:contains("Save changes")
${PRICES_S_DISCARD_BTN}			jquery=button:contains("Discard changes")

${PRICES_ADD_PRICE_MODAL_JQ}	jquery=price-modal-component #price-modal-component
${PRICES_ADD_NAME_ENG_JQ}		jquery=price-modal-component .modal-dialog label:contains("Name") tr:contains("English") select
${PRICES_ADD_AGE_RANGE_JQ}		jquery=price-modal-component .modal-dialog label:contains("Age Range") select
${PRICES_ADD_AMOUNT_JQ}			jquery=price-modal-component .modal-dialog label:contains("Amount") input
${PRICES_ADD_EXTRA_JQ}			jquery=price-modal-component .modal-dialog label:contains("Extra") input
${PRICES_ADD_SAVE_JQ}			jquery=price-modal-component .modal-dialog button:contains("Save")
${PRICES_ADD_CLEAR_JQ}			jquery=price-modal-component .modal-dialog button:contains("Cancel")

#DISCOUNTS:
${DISCOUNTS_S_NAME_TXT_INPUT}	css=body > app-component > div > ng-component > div > ng-component > div > div > form-component > form > form-element-component:nth-child(3) > fieldset > label > description-component > div > form-component > form > form-element-component:nth-child(1) > fieldset > label > input
${DISCOUNTS_S_NAME_LAN_INPUT}	css=body > app-component > div > ng-component > div > ng-component > div > div > form-component > form > form-element-component:nth-child(3) > fieldset > label > description-component > div > form-component > form > form-element-component:nth-child(2) > fieldset > label > select
${DISCOUNTS_S_NAME_SET_BTN}		css=body > app-component > div > ng-component > div > ng-component > div > div > form-component > form > form-element-component:nth-child(3) > fieldset > label > description-component > div > form-component > form > button.btn.btn-primary.btn-sm
${DISCOUNTS_S_NAME_TABLE_R}		xpath=/html/body/app-component/div/ng-component/div/ng-component/div/div/form-component/form/form-element-component[3]/fieldset/label/description-component/div/table-container/table/thead/tr
${DISCOUNTS_S_FROM_INPUT}		css=body > app-component > div > ng-component > div > ng-component > div > div > form-component > form > form-element-component:nth-child(4) > fieldset > label > input
${DISCOUNTS_S_TO_INPUT}			css=body > app-component > div > ng-component > div > ng-component > div > div > form-component > form > form-element-component:nth-child(5) > fieldset > label > input
${DISCOUNTS_S_TYPE_INPUT}		css=body > app-component > div > ng-component > div > ng-component > div > div > form-component > form > form-element-component:nth-child(6) > fieldset > label > select
${DISCOUNTS_S_OTHER_INPUT}		css=body > app-component > div > ng-component > div > ng-component > div > div > form-component > form > form-element-component:nth-child(7) > fieldset > label > select
${DISCOUNTS_S_SAVE_BTN}			css=body > app-component > div > ng-component > div > ng-component > div > div > form-component > form > button.btn.btn-primary.btn-sm
${DISCOUNTS_S_TABLE_R}			xpath=/html/body/app-component/div/ng-component/div/ng-component/div/table-container/table//tr

#GALLERY
${GALLERY_UPLOAD_BTN}		jquery=button:contains("Upload"):visible
${GALLERY_FILE_CHOOSER}		jquery=input[type="file"]
${GALLERY_UPLOAD_MODAL}		jquery=file-uploader-modal-component
${GALLERY_UPLOAD_ALL_BTN}	jquery=button:contains("Upload all")
${GALLERY_UPLOAD_DONE_BTN}	jquery=button:contains("Done")
${GALLERY_EDIT_SET_BTN}		jquery=button:contains("Set")
${GALLERY_ENG_DESC_JQ}		jquery=label:contains("Description") tr:contains("English") input
${GALLERY_ENG_NAME_JQ}		jquery=label:contains("Gallery name") tr:contains("English") input
${GALLERY_HIGHLIGHT_SEL_JQ}		jquery=label:contains("Highlighted") input
${GALLERY_ROLE_SEL_JQ}		jquery=label:contains("Gallery role") select

#DISCOUNTS
${DISC_ENG_NAME_INPUT_JQ}	jquery=label:contains("Name") table tr:contains("English") input
${DISC_MODIFIER_SEL}		jquery=label:contains("Modifier type") select
${DISC_ACTIVE_CHK_JQ}		jquery=label:contains("Active") input
${DISC_CARRY_CHK_JQ}		jquery=label:contains("Carry on price modifier") input
${DISC_DATE_RANGES_SEL}		jquery=label:contains("Price modifier date ranges") select
${DISC_PROMOTION_INPUT}		jquery=label:contains("Promotion code") input
${DISC_CONDITION_SEL}		jquery=label:contains("Condition") select
${DISC_OFFER_SEL}			jquery=label:contains("Offer") select
${DISC_OFFER_DESC_JQ}		jquery=label:contains("Offer description") table tr:contains("English") textarea
${DISC_OFFER_DESC_TMCE_JQ}	jquery=label:contains("Offer description") table tr:contains("English") iframe
${DISC_SAVE_BTN_JQ}			jquery=button:contains("Save")
${DISC_CLEAR_BTN_JQ}		jquery=button:contains("Clear")

@{DISCOUNT_PAGE_EXCEPTED}	${DISC_ENG_NAME_INPUT_JQ}	${DISC_MODIFIER_SEL}	${DISC_ACTIVE_CHK_JQ}		${DISC_CARRY_CHK_JQ}	${DISC_DATE_RANGES_SEL}		${DISC_PROMOTION_INPUT}		${DISC_CONDITION_SEL}	${DISC_OFFER_SEL}	${DISC_OFFER_DESC_TMCE_JQ}	${DISC_SAVE_BTN_JQ}		${DISC_CLEAR_BTN_JQ}		



#ALERTS!
${ALERT_ROOM_EXIST}		Room with same name exists.
${ALERT_ORG_EXIST}		Organization name already exists
${ALERT_PROP_EXIST}		There is already a property with the same name. Instead of creating a duplication try to edit the existing one!
${ALERT_TAX_EXIST}		There is already a taxonomy with the same name. Instead of creating a duplication try to use the existing one!
${ALERT_AGE_OVERLAP}	Age range overlap.
${ALERT_AGE_EXIST}		Age range name already used.
${ALERT_AGE_ACTIVE}		A model with active relations can not be deleted.
${ALERT_AGE_ADULT}		You cannot delete default (adult) age range.
${ALERT_PRICE_EXIST}	Price row with same name exists.
${ALERT_NO_QUERY_TAX}	No query results for model [Modules\\Stylerstaxonomy\\Entities\\Taxonomy].
${ALERT_DELETE_IMAGE}	Are you sure to delete this picture?

#CONTENTS:
${ADD_NEW_CONTENT_BUTTON}	            jquery=button:contains("Add new content...")
${ADD_NEW_CONTENT_MODAL}	            css=#new-content-component
${EDIT_CONTENT_MODAL}	                css=#new-content-component
${ADD_NEW_CONTENT_TITLE}            	jquery=#new-content-component div[data-formgroupname=title] input[data-formcontrolname=en]
${ADD_NEW_CONTENT_STATUS_SELECT}	    jquery=#new-content-component select[formcontrolname=status]
${ADD_NEW_CONTENT_LEAD} 	            jquery=#new-content-component div[data-formgroupname=lead] textarea[data-formcontrolname=en]
${ADD_NEW_CONTENT_CONTENT} 	            jquery=#new-content-component #content_en
${ADD_NEW_CONTENT_META_DESCRIPTION} 	jquery=#new-content-component div[data-formgroupname=meta_description] input[data-formcontrolname=en]
${ADD_NEW_CONTENT_META_KEYWORD} 	    jquery=#new-content-component div[data-formgroupname=meta_keyword] input[data-formcontrolname=en]
${ADD_NEW_CONTENT_META_TITLE} 	        jquery=#new-content-component div[data-formgroupname=meta_title] input[data-formcontrolname=en]
${ADD_NEW_CONTENT_SAVE}			        jquery=button[type=submit]:contains("Save")
${CONTENT_LIST_TABLE}                   jquery=#table-content-list

#CONTENT CATEGORIES:
${CONTENT_NAVIGATION_CATEGORIES}                jquery=.nav li a:contains("Categories")
${ADD_NEW_CONTENT_CATEGORY_BUTTON}	            jquery=button:contains("Add new category...")
${ADD_NEW_CONTENT_CATEGORY_MODAL}               css=#new-content-category-component
${EDIT_CONTENT_CATEGORY_MODAL}	                css=#new-content-category-component
${ADD_NEW_CONTENT_CATEGORY_ENGLISH}	            jquery=#new-content-category-component input[formcontrolname=name]
${ADD_NEW_CONTENT_CATEGORY_GERMAN}	            jquery=#new-content-category-component input[data-formcontrolname=de]
${ADD_NEW_CONTENT_CATEGORY_HUNGARIAN}	        jquery=#new-content-category-component input[data-formcontrolname=hu]
${ADD_NEW_CONTENT_CATEGORY_RUSSIAN}             jquery=#new-content-category-component input[data-formcontrolname=ru]
${ADD_NEW_CONTENT_CATEGORY_SAVE}    	        jquery=button[type=submit]:contains("Save")
${CONTENT_CATEGORY_LIST_TABLE}                  jquery=#table-content-category-list


${BODY_JQ}						jquery=body
${ORGANIZATIONS_NAV_TABS_JQ}	jquery=.nav-tabs a
${ORG_TABLE_ROW_BTNS_JQ}		jquery=tr:eq(1) button
${ORG_TABLE_JQ}					jquery=table
${ORG_DISC_COMBO_SAVE}			jquery=a:contains("Save")
${CLASSIFICATION_MODAL}			jquery=#classification-modal-component
${META_MODAL}					jquery=#meta-modal-component
${MODAL_TITLE}                  jquery=.modal-title
${UITOOL_MODAL}                 jquery=.modal

${TABLE}                        jquery=table
${TABLE_ROW_BTNS}		        jquery=tr:eq(1) button

@{CLASS_EDITOR_EXCEPTED}		${CLASS_ED_NAME_TABLE_JQ}	${CLASS_ED_NAME_ENG_IN_JQ}	${CLASS_ED_NAME_ENG_ADD_JQ}	${CLASS_ED_VALUE_TABLE_JQ}	${CLASS_ED_VALUE_ENG_IN_JQ}		${CLASS_ED_VALUE_ENG_ADD_JQ}	${CLASS_ED_CHARGE_TABLE_JQ}		${CLASS_ED_CHARGE_ENG_IN_JQ}	${CLASS_ED_CHARGE_ENG_ADD_JQ}	${CLASS_ED_DESC_TABLE_JQ}	${CLASS_ED_DESC_ENG_TMCE_JQ}	${CLASS_ED_HIGHLIGHT_SEL_JQ}	${CLASS_ED_LISTABLE_SEL_JQ}		${CLASS_ED_PRIORITY_IN_JQ}	${CLASS_ED_SAVE_BTN_JQ}		${CLASS_ED_CLEAR_BTN_JQ}

@{META_EDITOR_EXCEPTED}			${META_ED_NAME_TABLE_JQ}	${META_ED_NAME_ENG_IN_JQ}	${META_ED_NAME_ENG_ADD_JQ}	${META_ED_VALUE_INPUT_JQ}		${META_ED_DESC_TABLE_JQ}	${META_ED_DESC_ENG_TMCE_JQ}	${META_ED_LISTABLE_SEL_JQ}	${META_ED_PRIORITY_IN_JQ}		${META_ED_SAVE_BTN_JQ}		${META_ED_CLEAR_BTN_JQ}

&{SINGLE_SELECTORS}				Classification editor=${CLASSIFICATION_MODAL}	Meta editor=${META_MODAL}
&{GROUPED_SELECTORS}			Classification editor=@{CLASS_EDITOR_EXCEPTED}	Meta editor=@{META_EDITOR_EXCEPTED}

&{BUTTONS_WITH_GLYPHICON}		Edit=.glyphicon-pencil	Delete=.glyphicon-trash