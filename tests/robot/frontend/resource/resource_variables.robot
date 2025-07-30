*** Settings ***
Resource				resource_variables_languages.robot

*** Variables ***
${DATE_SEPARATOR}				/

${ADULTS_NUM}		${0}
${CHILDREN_NUM}		${0}
${ROOMS_NUM}		${0}

${GLOBAL_FROM}			${EMPTY}
${GLOBAL_TO}			${EMPTY}
${GLOBAL_GUESTS}		${EMPTY}
${GLOBAL_TYPE}			${EMPTY}
${GLOBAL_NAME}			${EMPTY}
${GLOBAL_ISLANDS}		${EMPTY}
${GLOBAL_MP}			${EMPTY}
${GLOBAL_CATEGORIES}	${EMPTY}
${GLOBAL_SORT}			${EMPTY}
${GLOBAL_CHECK}			${EMPTY}
${GLOBAL_DETAILS}		${EMPTY}
${GLOBAL_HOLIDAY}		${EMPTY}

&{GLOBAL_RESPONSE}		&{EMPTY}

${MAIN_HEADER_LOGO_JQ}			jquery=.navbar a:contains("seychelles.us")
${MAIN_NAV_MAIN_PAGE_JQ}		jquery=.navbar a:contains("@{LANG_MENU_HOME}[${LAN}]")
${MAIN_NAV_SEARCH_JQ}			jquery=.navbar a:contains("@{LANG_MENU_SEARCH}[${LAN}]")
${MAIN_NAV_MYHOLYDAY}			jquery=.navbar a:contains("@{LANG_MENU_MYHOLYDAY}[${LAN}]")
${MAIN_NAV_MYHOLYDAY_BADGE}		jquery=.navbar li:contains("@{LANG_MENU_MYHOLYDAY}[${LAN}]") span
${MAIN_NAV_ABOUT}				jquery=.navbar a:contains("About us")
${MAIN_NAV_ABOUT_SEYC}			jquery=.navbar a:contains("About Seychelles")
${MAIN_NAV_CONTACT_US}			jquery=.navbar a:contains("Contact us")
${MAIN_NAV_TEL}					jquery=.navbar div:contains("+36 (70) 258 2403")
${MAIN_NAV_M_BASKET}			jquery=.navbar .ots-icon-basket
${MAIN_NAV_M_ICON_BAR}			jquery=.navbar .icon-bar
${MAIN_NAV_M_ICON_CLOSE}		jquery=.navbar .close


@{EXPECTED_LIST_HEADER}			${MAIN_HEADER_LOGO_JQ}		${MAIN_NAV_MAIN_PAGE_JQ}	${MAIN_NAV_SEARCH_JQ}	${MAIN_NAV_MYHOLYDAY}	${MAIN_NAV_ABOUT}	${MAIN_NAV_ABOUT_SEYC}	${MAIN_NAV_CONTACT_US}	${MAIN_NAV_TEL}		${MAIN_NAV_M_BASKET}	${MAIN_NAV_M_ICON_BAR}	${MAIN_NAV_M_ICON_CLOSE}

#${MAIN_SEARCH_TITLE}			jquery=.home-searcher .container div:contains("Find your perfect Seychelles accommodation"):eq(1)
${MAIN_SEARCH_TITLE}			jquery=.home-searcher .container div:contains("Find your perfect"):eq(1)
${MAIN_SEARCH_FROM_TITLE}		jquery=.home-searcher .container .label:contains("@{LANG_FROM}[${LAN}]")
${MAIN_SEARCH_FROM}				jquery=.home-searcher .container input.from_date
${MAIN_SEARCH_TO_TITLE}			jquery=.home-searcher .container .label:contains("@{LANG_TO}[${LAN}]")
${MAIN_SEARCH_TO}				jquery=.home-searcher .container input.end_date
${MAIN_SEARCH_GUESTS_TITLE}		jquery=.home-searcher .container .label:contains("@{LANG_GUESTS}[${LAN}]")
${MAIN_SEARCH_GUESTS}			jquery=.home-searcher .container button[href="#collapseSeeker"]
${MAIN_SEARCH_SEND}				jquery=.home-searcher .container button:contains("@{LANG_SEARCH}[${LAN}]")
${MAIN_SEARCH_GU_ADULTS_TITLE}	jquery=.home-searcher .room-box .form-label:contains("@{LANG_ADULTS}[${LAN}]")
${MAIN_SEARCH_GU_ADULTS}		jquery=.room-box button:eq(0)
#${MAIN_SEARCH_GU_ADULTS}		jquery=.home-searcher .room-box button:eq(0)
${MAIN_SEARCH_GU_ROOM_BLOCK}	jquery=.usage-selector .border
#${MAIN_SEARCH_GU_ROOM_BLOCK}	jquery=.room-selector .border
${MAIN_SEARCH_GU_CHILDREN_TITLE}	jquery=.room-box .form-label:contains("@{LANG_CHILDREN}[${LAN}]")
#${MAIN_SEARCH_GU_CHILDREN_TITLE}	jquery=.home-searcher .room-box .form-label:contains("@{LANG_CHILDREN}[${LAN}]")
${MAIN_SEARCH_GU_CHILDREN}		jquery=.room-box button:eq(1)
#${MAIN_SEARCH_GU_CHILDREN}		jquery=.home-searcher .room-box button:eq(1)
${MAIN_SEARCH_GU_ADD_ROOM}		jquery=button:contains("@{LANG_ADD_ROOM}[${LAN}]")
#${MAIN_SEARCH_GU_ADD_ROOM}		jquery=button:contains("@{LANG_ADD_ROOM}[${LAN}]")
${MAIN_SEARCH_GU_OPTION}		jquery=.room-box li
#${MAIN_SEARCH_GU_OPTION}		jquery=.home-searcher .room-box li
${MAIN_SEARCH_HOLIDAY_RADIO}	jquery=.home-searcher .container .radio-container:contains("@{LANG_HOLIDAY}[${LAN}]") input
${MAIN_SEARCH_HONEYMOON_RADIO}	jquery=.home-searcher .container .radio-container:contains("@{LANG_HONEYMOON}[${LAN}]") input
${MAIN_SEARCH_ANNIVERSARY_RADIO}	jquery=.home-searcher .container .radio-container:contains("@{LANG_ANNIVERSARY}[${LAN}]") input
${MAIN_SEARCH_RETURNING}		jquery=.home-searcher .checkbox-container:has(label:contains("@{LANG_RETURNING}[${LAN}]"))

${MAIN_SEARCH_DATE_PANEL}			jquery=.date-picker-wrapper
${MAIN_SEARCH_DATE_SIDE_YEAR_FROM}	jquery=.side-year.first
${MAIN_SEARCH_DATE_SIDE_DATE_FROM}	jquery=.side-date.first
${MAIN_SEARCH_DATE_SIDE_YEAR_TO}	jquery=.side-year.last
${MAIN_SEARCH_DATE_SIDE_DATE_TO}	jquery=.side-date.last
${MAIN_SEARCH_DATE_LEFT_ARROW}		jquery=.prev
${MAIN_SEARCH_DATE_RIGHT_ARROW}		jquery=.next
${MAIN_SEARCH_DATE_HEADER_FROM}		jquery=.month-name:eq(0)
${MAIN_SEARCH_DATE_HEADER_TO}		jquery=.month-name:eq(1)
${MAIN_SEARCH_DATE_WEEK_NAME_FROM}	jquery=.week-name:eq(0)
${MAIN_SEARCH_DATE_WEEK_NAME_TO}	jquery=.week-name:eq(1)
${MAIN_SEARCH_DATE_DAY_FROM_BASE}	jquery=.month1 .day.toMonth
${MAIN_SEARCH_DATE_DAY_TO_BASE}		jquery=.month2 .day.toMonth
${MAIN_SEARCH_DATE_D_KNOW_DATES_LAB}	jquery=.checkbox-container:contains("@{LANG_DONTKNOWDATES}[${LAN}]") label
${MAIN_SEARCH_DATE_D_KNOW_DATES}	jquery=.checkbox-container:contains("@{LANG_DONTKNOWDATES}[${LAN}]") input
${MAIN_SEARCH_DATE_NIGHTS}			jquery=.night-container
${MAIN_SEARCH_DATE_CANCEL}			jquery=.date-picker-wrapper button:contains("@{LANG_CANCEL}[${LAN}]")
${MAIN_SEARCH_DATE_OK}				jquery=.apply-btn

${DEFAULT_YEAR_SELECTOR}			${MAIN_SEARCH_DATE_HEADER_FROM}
${DEFAULT_DAY_SELECTOR}				${MAIN_SEARCH_DATE_DAY_FROM_BASE}


@{EXPECTED_LIST_SEARCH}			${MAIN_SEARCH_TITLE}	${MAIN_SEARCH_FROM_TITLE}	${MAIN_SEARCH_FROM}	${MAIN_SEARCH_TO_TITLE}		${MAIN_SEARCH_TO}	${MAIN_SEARCH_GUESTS_TITLE}		${MAIN_SEARCH_GUESTS}	${MAIN_SEARCH_SEND}		${MAIN_SEARCH_HOLIDAY_RADIO}	${MAIN_SEARCH_HONEYMOON_RADIO}		${MAIN_SEARCH_ANNIVERSARY_RADIO}

@{EXPECTED_LIST_GUESTS_EDIT}	${MAIN_SEARCH_GU_ADULTS_TITLE}	${MAIN_SEARCH_GU_ADULTS}	${MAIN_SEARCH_GU_CHILDREN_TITLE}	${MAIN_SEARCH_GU_CHILDREN}

@{EXPECTED_LIST_DATE_PICKER}	${MAIN_SEARCH_DATE_SIDE_YEAR_FROM}	${MAIN_SEARCH_DATE_SIDE_DATE_FROM}	${MAIN_SEARCH_DATE_SIDE_YEAR_TO}	${MAIN_SEARCH_DATE_SIDE_DATE_TO}	${MAIN_SEARCH_DATE_LEFT_ARROW}	${MAIN_SEARCH_DATE_RIGHT_ARROW}	${MAIN_SEARCH_DATE_HEADER_FROM}	${MAIN_SEARCH_DATE_HEADER_TO}	${MAIN_SEARCH_DATE_WEEK_NAME_FROM}	${MAIN_SEARCH_DATE_WEEK_NAME_TO}	${MAIN_SEARCH_DATE_DAY_FROM_BASE}	${MAIN_SEARCH_DATE_DAY_TO_BASE}	${MAIN_SEARCH_DATE_D_KNOW_DATES}	${MAIN_SEARCH_DATE_NIGHTS}	${MAIN_SEARCH_DATE_CANCEL}	${MAIN_SEARCH_DATE_OK}

@{EXPECTED_LIST_DATE_PICKER_M}	${MAIN_SEARCH_DATE_SIDE_YEAR_FROM}	${MAIN_SEARCH_DATE_SIDE_DATE_FROM}	${MAIN_SEARCH_DATE_SIDE_YEAR_TO}	${MAIN_SEARCH_DATE_SIDE_DATE_TO}	${MAIN_SEARCH_DATE_LEFT_ARROW}	${MAIN_SEARCH_DATE_RIGHT_ARROW}	${MAIN_SEARCH_DATE_HEADER_FROM}		${MAIN_SEARCH_DATE_WEEK_NAME_FROM}	${MAIN_SEARCH_DATE_DAY_FROM_BASE}	${MAIN_SEARCH_DATE_D_KNOW_DATES}	${MAIN_SEARCH_DATE_NIGHTS}	${MAIN_SEARCH_DATE_CANCEL}	${MAIN_SEARCH_DATE_OK}

${MAIN_INFO_TITLE1}			jquery=p:contains("We think planning a holiday to Seychelles and making the right travel choices should be easy.")
${MAIN_INFO_TITLE2}			jquery=p:contains("Just as finding your ideal place to stay and booking on-site services should be possible from your")
${MAIN_INFO_CARD1_TITLE}	jquery=.card div:contains("Unique and specialized")
${MAIN_INFO_CARD1_TX}		jquery=.card div:contains("Create a unique holiday with specialized travel advisors")
${MAIN_INFO_CARD2_TITLE}	jquery=.card div:contains("Instant and real time")
${MAIN_INFO_CARD2_TX}		jquery=.card div:contains("Book instantly using real time availability")
${MAIN_INFO_CARD3_TITLE}	jquery=.card div:contains("Confident and safe")
${MAIN_INFO_CARD3_TX}		jquery=.card div:contains("Be confident and safe in Seychelles")

@{EXPECTED_LIST_INFO}		${MAIN_INFO_TITLE1}	${MAIN_INFO_TITLE2}	${MAIN_INFO_CARD1_TITLE}	${MAIN_INFO_CARD1_TX}	${MAIN_INFO_CARD2_TITLE}	${MAIN_INFO_CARD2_TX}	${MAIN_INFO_CARD3_TITLE}	${MAIN_INFO_CARD3_TX}

${MAIN_BLOG_DEF_TITLE}		jquery=.card .title
${MAIN_BLOG_DEF_IMG}		jquery=.card .img-responsive
${MAIN_BLOG_DEF_DESC}		jquery=.card .description
${MAIN_BLOG_DEF_LINK}		jquery=.card .link

@{EXPECTED_LIST_BLOG}		${MAIN_BLOG_DEF_TITLE}	${MAIN_BLOG_DEF_IMG}		${MAIN_BLOG_DEF_DESC}	${MAIN_BLOG_DEF_LINK}

${MAIN_FOOTER_ACCOMODATION}				jquery=.footer li a:contains("@{LANG_ACCOMODATION}[${LAN}]")
${MAIN_FOOTER_CRUISES}					jquery=.footer li a:contains("@{LANG_CRUISES}[${LAN}]")
${MAIN_FOOTER_YACHT_CHARTER}			jquery=.footer li a:contains("@{LANG_YACHT_CHARTER}[${LAN}]")
${MAIN_FOOTER_WEDDINGS}					jquery=.footer li a:contains("@{LANG_WEDDINGS}[${LAN}]")
${MAIN_FOOTER_TRANSFER}					jquery=.footer li a:contains("@{LANG_TRANSFER}[${LAN}]")
${MAIN_FOOTER_MY_HOLIDAY}				jquery=.footer li a:contains("@{LANG_MY_HOLIDAY}[${LAN}]")
${MAIN_FOOTER_BLOG}						jquery=.footer li a:contains("@{LANG_BLOG}[${LAN}]")
${MAIN_FOOTER_WEBCAM}					jquery=.footer li a:contains("@{LANG_WEBCAM}[${LAN}]")
${MAIN_FOOTER_WEATHER}					jquery=.footer li a:contains("@{LANG_WEATHER}[${LAN}]")
${MAIN_FOOTER_ACTIVITES}				jquery=.footer li a:contains("@{LANG_ACTIVITES}[${LAN}]")
${MAIN_FOOTER_USEFUL_TIPS}				jquery=.footer li a:contains("@{LANG_USEFUL_TIPS}[${LAN}]")
${MAIN_FOOTER_ABOUT_US}					jquery=.footer li a:contains("@{LANG_ABOUT_US}[${LAN}]")
${MAIN_FOOTER_CONTACT}					jquery=.footer li a:contains("@{LANG_CONTACT}[${LAN}]")
${MAIN_FOOTER_ALL_RIGHTS_RESERVED}		jquery=.bottom-menu div:contains("@{LANG_ALL_RIGHTS_RESERVED}[${LAN}]")
${MAIN_FOOTER_TERMS_AND_CONDITIONS}		jquery=.bottom-menu a:contains("@{LANG_TERMS_AND_CONDITIONS}[${LAN}]")
${MAIN_FOOTER_BOOKING_POLICY}			jquery=.bottom-menu a:contains("@{LANG_BOOKING_POLICY}[${LAN}]")
${MAIN_FOOTER_PRIVACY_POLICY}			jquery=.bottom-menu a:contains("@{LANG_PRIVACY_POLICY}[${LAN}]")
${MAIN_LANGUAGE_SELECTOR}				jquery=.footer-selector .dropdown:visible:eq(0)
${MAIN_CURRENCY_SELECTOR}				jquery=.footer-selector .dropdown:visible:eq(1)

@{EXPECTED_LIST_FOOTER}					${MAIN_FOOTER_ACCOMODATION}	${MAIN_FOOTER_CRUISES}	${MAIN_FOOTER_YACHT_CHARTER}	${MAIN_FOOTER_WEDDINGS}	${MAIN_FOOTER_TRANSFER}	${MAIN_FOOTER_MY_HOLIDAY}	${MAIN_FOOTER_BLOG}	${MAIN_FOOTER_WEBCAM}	${MAIN_FOOTER_WEATHER}	${MAIN_FOOTER_ACTIVITES}	${MAIN_FOOTER_USEFUL_TIPS}	${MAIN_FOOTER_ABOUT_US}	${MAIN_FOOTER_CONTACT}	${MAIN_FOOTER_ALL_RIGHTS_RESERVED}	${MAIN_FOOTER_TERMS_AND_CONDITIONS}	${MAIN_FOOTER_BOOKING_POLICY}	${MAIN_FOOTER_PRIVACY_POLICY}	${MAIN_LANGUAGE_SELECTOR}	${MAIN_CURRENCY_SELECTOR}

${SEARCH_SUM_FROM_TO}					jquery=.search-summary li:eq(0)
${SEARCH_SUM_NIGHTS}					jquery=.search-summary li:contains("@{LANG_NIGHTS}[${LAN}]")
${SEARCH_SUM_GUESTS}					jquery=.search-summary li:eq(2)
${SEARCH_SUM_RESULTS}					jquery=.search-summary li:contains("@{LANG_RESULTS}[${LAN}]")
${SEARCH_SUM_LIST_VIEW}					jquery=.search-summary li a:contains("@{LANG_LIST_VIEW}[${LAN}]")
${SEARCH_SUM_MAP_VIEW}					jquery=.search-summary li a:contains("@{LANG_MAP_VIEW}[${LAN}]")
${SEARCH_SUM_SORT_BY_BTN}				jquery=.search-summary button:contains("@{LANG_BYPRICE_ASC}[${LAN}]")

${SEARCH_FILTER_ACC_NAME}				jquery=#sidebar div:contains("@{LANG_ACCOMMODATION_NAME}[${LAN}]") input
${SEARCH_FILTER_FROM_TTLE}				jquery=#sidebar .label:contains("@{LANG_FROM}[${LAN}]")
${SEARCH_FILTER_FROM}					jquery=input.from_date
${SEARCH_FILTER_TO_TTLE}				jquery=#sidebar .label:contains("@{LANG_TO}[${LAN}]")
${SEARCH_FILTER_TO}						jquery=input.end_date
${SEARCH_FILTER_GUESTS_TITLE}			jquery=#sidebar .label:contains("@{LANG_GUESTS}[${LAN}]")
#${SEARCH_FILTER_GUESTS}					jquery=#sidebar div:contains("@{LANG_GUESTS}[${LAN}]") button
${SEARCH_FILTER_REMOVE_ROOM}			jquery=.room-box button:contains("@{LANG_REMOVE}[${LAN}]")
${SEARCH_FILTER_GUESTS}					jquery=.usage-selector
${SEARCH_FILTER_HOLIDAY_RADIO}			jquery=#sidebar .radio-container:contains("@{LANG_HOLIDAY}[${LAN}]")
${SEARCH_FILTER_HONEYMOON_RADIO}		jquery=#sidebar .radio-container:contains("@{LANG_HONEYMOON}[${LAN}]")
${SEARCH_FILTER_ANNIVERSARY_RADIO}		jquery=#sidebar .radio-container:contains("@{LANG_ANNIVERSARY}[${LAN}]")
${SEARCH_FILTER_ANNIVERSARY_DATE}		jquery=.wedding-date
${SEARCH_FILTER_SEND}					jquery=button:contains("@{LANG_MENU_SEARCH}[${LAN}]")

${MAIN_SINGLE_DATE_PANEL}			jquery=.single-date
${MAIN_SINGLE_DATE_LEFT_ARROW}		jquery=.single-date .prev
${MAIN_SINGLE_DATE_RIGHT_ARROW}		jquery=.single-date .next
${MAIN_SINGLE_DATE_HEADER_FROM}		jquery=.single-date .month-name:eq(0)
${MAIN_SINGLE_DATE_WEEK_NAME_FROM}	jquery=.single-date .week-name:eq(0)
${MAIN_SINGLE_DATE_DAY_FROM_BASE}	jquery=.single-date .month1 .day.toMonth
${MAIN_SINGLE_DATE_YEAR_INPUT}		jquery=#datepicker-input-year
${MAIN_SINGLE_DATE_CLOSE}			jquery=.single-date .apply-btn

${SEARCH_FILTER_ISLAND_BLOCK}			jquery=.island-block
${SEARCH_FILTER_ISLAND_B_TITLE}			jquery=.island-block .label:contains("@{LANG_ISLANDS}[${LAN}]")
${SEARCH_FILTER_ISLAND_B_MAHE_CHK}		jquery=#sidebar .checkbox-container:contains("Mahé")
${SEARCH_FILTER_ISLAND_B_MAHE_CHK}		jquery=#sidebar .checkbox-container:contains("Praslin")
${SEARCH_FILTER_ISLAND_B_MAHE_CHK}		jquery=#sidebar .checkbox-container:contains("La Dique")
${SEARCH_FILTER_ISLAND_B_MAHE_CHK}		jquery=#sidebar .checkbox-container:contains("Cerf")
${SEARCH_FILTER_ISLAND_B_MAHE_CHK}		jquery=#sidebar .checkbox-container:contains("St. Anne")

${SEARCH_FILTER_MEAL_PLAN_BLOCK}		jquery=.meal-plan-form-component
${SEARCH_FILTER_M_P_B_TITLE}			jquery=.meal-plan-form-component .label:contains("@{LANG_MEAL_PLANS}[${LAN}]")
${SEARCH_FILTER_M_P_B_EMPTY_CHK}		jquery=#sidebar .checkbox-container:contains("@{LANG_MP_EP}[${LAN}]")
${SEARCH_FILTER_M_P_B_BB_CHK}			jquery=#sidebar .checkbox-container:contains("@{LANG_MP_BB}[${LAN}]")
${SEARCH_FILTER_M_P_B_HB_CHK}			jquery=#sidebar .checkbox-container:contains("@{LANG_MP_HB}[${LAN}]")
${SEARCH_FILTER_M_P_B_FB_CHK}			jquery=#sidebar .checkbox-container:contains("@{LANG_MP_FB}[${LAN}]")
${SEARCH_FILTER_M_P_B_INC_CHK}			jquery=#sidebar .checkbox-container:contains("@{LANG_MP_INC}[${LAN}]")

${SEARCH_ALERT_WARNING}                         jquery=.hotel-result-container .alert.alert-warning

#${RESULTS_SEARCH_LIST_ITEM}					jquery=.search-list-item
${RESULTS_SEARCH_LIST_ITEM}					jquery=.product-card
${RESULTS_SEARCH_LIST_IMAGE}				jquery=.product-card .list-item-image img
${RESULTS_SEARCH_LIST_MAP}					jquery=.product-card .list-item-map
#${RESULTS_SEARCH_LIST_HOTEL_NAME}			jquery=.product-card .headline h3
${RESULTS_SEARCH_LIST_HOTEL_NAME}			jquery=.product-card .heading
${RESULTS_SEARCH_LIST_ACC_DETAILS}			jquery=.product-card .accommodation-details
${RESULTS_SEARCH_LIST_HOTEL_DETAILS}		jquery=.product-card .hotel-details
${RESULTS_SEARCH_LIST_RATE}					jquery=.product-card .headline .rating
${RESULTS_SEARCH_LIST_RESERVATION_DETAILS}	jquery=.product-card .reservation-details
${RESULTS_SEARCH_LIST_PRICE_LEVEL}			jquery=.product-card .price-level
${RESULTS_SEARCH_LIST_BEST_PRICE}			jquery=.product-card .best-price
${RESULTS_SEARCH_LIST_PRICE_INFO}			jquery=.product-card .price-info
${RESULTS_SEARCH_LIST_SEE_ALL}				jquery=.product-card .see-all a:visible:contains(@{LANG_SEE_ALL_ROOMS}[${LAN}])
${RESULTS_SEARCH_SORT_BY}					jquery=div.dropdown:visible:contains(@{LANG_SORT_BY}[${LAN}])

${HOTEL_PAGE_ROOM_PRICES}					jquery=.price-list:visible label

${SIDEBAR}                                  jquery=.sidebar
${CONTENTS_BY_CATEGORY}                     jquery=.contents-by-categories

&{RESULTS_DICTIONARY}
...		${RESULTS_SEARCH_LIST_ITEM}=List Item
...		${RESULTS_SEARCH_LIST_IMAGE}=Image
...		${RESULTS_SEARCH_LIST_MAP}=Map
...		${RESULTS_SEARCH_LIST_HOTEL_NAME}=Hotel Name
...		${RESULTS_SEARCH_LIST_ACC_DETAILS}=Accommodation Details
...		${RESULTS_SEARCH_LIST_HOTEL_DETAILS}=Hotel Details
...		${RESULTS_SEARCH_LIST_RATE}=Rate
...		${RESULTS_SEARCH_LIST_RESERVATION_DETAILS}=Reservation Details
...		${RESULTS_SEARCH_LIST_PRICE_LEVEL}=Price Level
...		${RESULTS_SEARCH_LIST_BEST_PRICE}=Best Price
...		${RESULTS_SEARCH_LIST_PRICE_INFO}=Price Info
...		${RESULTS_SEARCH_LIST_SEE_ALL}=See All Rooms

${HOTEL_DESC_HEADER_NAME}					jquery=.header #hotel-name

${SEARCH_FILTER_MORE_OPTIONS_BLOCK}		jquery=.more-options-form-component
${SEARCH_FILTER_HOTEL_CATEGORY_BLOCK}	jquery=.more-options-form-component .label:contains("@{LANG_HOTEL_CATEGORY}[${LAN}]")
${SEARCH_FILTER_M_O_B_H_C_HOTEL_CHK}	jquery=#sidebar .checkbox-container:contains("hotel")
${SEARCH_FILTER_M_O_B_H_C_APARTMENT_CHK}	jquery=#sidebar .checkbox-container:contains("apartment")
${SEARCH_FILTER_M_O_B_H_C_RESORT_CHK}	jquery=#sidebar .checkbox-container:contains("resort")
${SEARCH_FILTER_M_O_B_H_C_VILLAS_CHK}	jquery=#sidebar .checkbox-container:contains("villas")
${SEARCH_FILTER_M_O_B_H_C_HOSTEL_CHK}	jquery=#sidebar .checkbox-container:contains("hostel")

${HOTEL_PAGE_ADD_TO_HOLIDAY}			jquery=button:contains("@{LANG_ADD_MY_HOLYDAY}[${LAN}]"):visible

${MY_HOLIDAY_FIRST_CARD}				jquery=.panel-container:has(.row):eq(0)
${MY_HOLIDAY_FORM_FIRST_NAME}			jquery=input[formcontrolname=first_name]
${MY_HOLIDAY_FORM_LAST_NAME}			jquery=input[formcontrolname=last_name]
${MY_HOLIDAY_FORM_COUNTRY}				jquery=mat-select
${MY_HOLIDAY_FORM_EMAIL}				jquery=input[formcontrolname=email]
${MY_HOLIDAY_FORM_TELEPHONE}			jquery=input[formcontrolname=telephone]

${MY_HOLIDAY_GUEST_INFO}				jquery=.stepper a:contains("@{LANG_MY_HOLIDAY_GUEST_INFO}[${LAN}]")
${MY_HOLIDAY_SUMMARY}					jquery=.stepper a:contains("@{LANG_MY_HOLIDAY_SUMMARY}[${LAN}]")
${MY_HOLIDAY_BOOK}						jquery=.stepper a:contains("@{LANG_MY_HOLIDAY_BOOK}[${LAN}]")
${MY_HOLIDAY_GUESTS_INPUT_DEF}			jquery=input:visible
${MY_HOLIDAY_BOOKING_SUCCESS_OK}		jquery=app-material-dialog .mat-dialog-actions button

${MSG_BOOKING_SUCCESS}					We have registered your order. Thank you!
