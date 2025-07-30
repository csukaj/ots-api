*** Variables ***
#Language				0=Eng		1=Hun
#@{LANGUAGES_AS_LIST_BY_SEQUENCE}=		en	hu
@{LANGUAGES_AS_LIST_BY_SEQUENCE}=		English		Hungarian
${LAN}					${0}
#&{LANGUAGES_DICT}		en=${0}		hu=${1}
&{LANGUAGES_DICT}		@{LANGUAGES_AS_LIST_BY_SEQUENCE}[0]=${0}		@{LANGUAGES_AS_LIST_BY_SEQUENCE}[1]=${1}

@{LANG_LOADING}			Loading		Betöltés
@{LANG_NO_RESULTS}		No matching results found.		Nincs találat a keresésre.
@{LANG_READMORE}		Read more	További információk
@{LANG_SUBMIT}			Submit		Elküld
@{LANG_TITLE}			Main page	Főoldal
@{LANG_WELC_TX}			Welcome to OTS Frontend!	Üdvözlünk az OTS Frontenden!
@{LANG_ROOM}			room		szoba
@{LANG_ROOMS}			rooms		szoba
@{LANG_GUESTS}			Guests		Vendégek
@{LANG_AGE_OF_CHILDREN}		Age of children at the time of check out	A gyermekek életkora a nyaralás végén
@{LANG_FROM}			From		Érkezés
@{LANG_TO}				To			Távozás
@{LANG_ARRIVAL}			Arrival		Érkezés
@{LANG_ARRIVAL}			Arrival		Érkezés
@{LANG_DEPARTURE}		Departure	Távozás
@{LANG_NIGHTS}			Nights		Éjszaka
@{LANG_REMOVE}			Remove		Törlés
@{LANG_OK}				Ok			Ok
@{LANG_CANCEL}			Cancel		Mégse
@{LANG_SELECT_DATES}	Please select the dates of your holiday.	Kérlek válaszd ki nyaralásod dátumát!
@{LANG_SEARCH}			Search		Keresés
@{LANG_ADD_ROOM}		Add room	Szoba hozzáadása
@{LANG_REMOVE}			Remove		Törlés
@{LANG_ROOM_WI}			Room #{{index}}		{{index}}. szoba
@{LANG_ADULTS}			Adults		Felnőttek
@{LANG_CHILDREN}		Children	Gyermekek
@{LANG_HOLIDAY}			Holiday		Nyaralás
@{LANG_HONEYMOON}		Honeymoon		Nászút
@{LANG_ANNIVERSARY}		Anniversary		Évforduló
@{LANG_RETURNING}		Returning client	Visszatérő vendég
@{LANG_ISLAND}			Island		Sziget
@{LANG_ISLANDS}			Islands		Szigetek
@{LANG_DONTKNOWDATES}	I don\'t know the dates yet		Nem tudom az érkezésem dátumát
@{LANG_DONTKNOWALERT}	Please select date range or check "I don't know the dates yet" checkbox!	Kérem válasszon dátumot vagy jelölje be a \"Nem tudom az érkezésem dátumát\" mezőt!
@{LANG_MEAL_PLANS}		Meal plans		Ellátás
@{LANG_ACCOMMODATION_NAME}	Accommodation name		Szállás neve
@{LANG_DESTINATION_NAME}	Destination name		Célállomás neve
@{LANG_FILTER}			Filter		Szűrő
@{LANG_SORT_BY}			Sort by		Rendezés
@{LANG_RESULTS}			results		találat
@{LANG_NIGTHS}			nights		éjszaka
@{LANG_LIST_VIEW}		List View	Lista
@{LANG_MAP_VIEW}		Map View	Térkép
@{LANG_BYPRICE_ASC}		Sort by Price (Low to High)		Ár szerint növekvő
@{LANG_BYPRICE_DESC}	Sort by Price (High to Low)		Ár szerint csökkenő
@{LANG_BYISLAND}		Sort by Island		Sziget szerint
@{LANG_MENU_HOME}		Home		Főoldal
@{LANG_MENU_SEARCH}		Search		Keresés
@{LANG_MENU_MYHOLYDAY}	My Holiday		Nyaralásom
@{LANG_MENU_TOGGLE}		Toggle navigation	Meni ki/be
@{LANG_AV_ROOM_RATES}	Available room rates	Elérhető szobaárak
@{LANG_HOTEL_DETAILS}	Hotel details	Szálloda részletes adatai
@{LANG_BACK_TO_LIST}	Back to all hotels	Vissza az összes szállodához
@{LANG_SEE_ALL_ROOMS}	See all rooms	Összes szoba mutatása
@{LANG_H_ROOM}			Room		Szoba
@{LANG_H_ROOMS}			Rooms		Szobák
@{LANG_H_ROOM_DET}		Room details	További információk
@{LANG_ADD_MY_HOLYDAY}	Add to my holiday	Hozzáadás a nyaralásomhoz
@{LANG_H_CHANGE_ROOM}	Change room		Szoba változtatása
@{LANG_H_GALLERY}		Gallery		Galéria
@{LANG_H_DESC}			Description		Leírás
@{LANG_H_LOCATION}		Location		Elhelyezkedés
@{LANG_H_REVIEWS}		Reviews		Értékelések
@{LANG_H_TOP_SERVICES}	Top Services		Kiemelt szolgáltatások
@{LANG_H_ABOUT}			About this hotel		A hotelről
@{LANG_H_DETAILS}		Details		Részletek
@{LANG_H_PROPERTIES}	Properties		Tulajdonságok
@{LANG_ISLAND_MAHE}			Mahé	Mahé
@{LANG_ISLAND_LA_DIGUE}		La Digue	La Digue
@{LANG_ISLAND_PRASLIN}		Praslin		Praslin
@{LANG_ISLAND_CERF}			Cerf	Cerf
@{LANG_ISLAND_ST_ANNE}		St. Anne	St. Anne
@{LANG_MP_EP}			Empty plan		Ellátás nélkül
@{LANG_MP_BB}			Bed & breakfast		Reggeli
@{LANG_MP_HB}			Half board		Félpanzió
@{LANG_MP_FB}			Full board		Teljes panzió
@{LANG_MP_INC}			All inclusive		All inclusive
@{LANG_HC_HOTEL}			Hotel		Hotel
@{LANG_HC_LUX_HOTEL}		Luxury Hotel		Luxury Hotel
@{LANG_HC_APARTMENT}		Apartment		Apartment
@{LANG_HC_VILLA}			Villa		Villa
@{LANG_HC_GUEST_HOUSE}		Guest House		Guest House
@{LANG_HC_PRIVATE_ROOM}		Private Room		Private Room
@{LANG_CLEAR_ALL}		Clear all items		Összes törlése
@{LANG_DELETE}			Delete		Törlés
@{LANG_NIGHTS}			nights		éjszaka
@{LANG_TOTAL}			Total:		Összesen:
@{LANG_AR_ADULT}		adult		felnőtt
@{LANG_AR_ADULTS}		adults		felnőtt
@{LANG_AR_CHILD}		child		gyerek
@{LANG_AR_CHILDREN}		children	gyerek
@{LANG_AR_BABY}			baby		csecsemő
@{LANG_AR_BABIES}		babies		csecsemő
@{LANG_DISC_TOTAL_DISC}		Total discount		Összes kevezmény
@{LANG_DISC_USED}		Discounts used		Alkalmazott kedvezmények
@{LANG_DISC}			Discounts		Kedvezmények
@{LANG_DISC_INC_TAX}	incl. Tax		adóval
@{LANG_DISC_ORIG_PRICE}		original price		Eredeti ár
@{LANG_DISC_NONE_FOUND}		No discounts found.		Nincs kedvezmény.
@{LANG_DISC_TOTAL}		Total		Összesen
@{LANG_MY_HOLIDAY_GUEST_INFO}	Guest information	Vendég adatai
@{LANG_MY_HOLIDAY_SUMMARY}		Summary		Összesítő
@{LANG_MY_HOLIDAY_BOOK}			Book now	Foglalás

@{LANG_ACCOMODATION}			Accommodation	Szállás
@{LANG_CRUISES}					Cruises		Utazások
@{LANG_YACHT_CHARTER}			Yacht charter	Yacht bérlés
@{LANG_WEDDINGS}				Weddings	Menyegző
@{LANG_TRANSFER}				Transfer	Átvitel
@{LANG_MY_HOLIDAY}				My Holiday	Nyaralásom
@{LANG_BLOG}					Blog	Blog
@{LANG_WEBCAM}					Webcam	Webkamera
@{LANG_WEATHER}					Weather		Időjárás
@{LANG_ACTIVITES}				Activites	Aktivitás
@{LANG_USEFUL_TIPS}				Useful tips		Hasznos tippek
@{LANG_ABOUT_US}				About us	Rólunk
@{LANG_CONTACT}					Contact		Kapcsolat

@{LANG_ALL_RIGHTS_RESERVED}			All Rights Reserved		Minden jog fenntartva
@{LANG_TERMS_AND_CONDITIONS}		Terms and Conditions	Felhasználási feltételek
@{LANG_BOOKING_POLICY}				Booking Policy		Foglalási szabályzat
@{LANG_PRIVACY_POLICY}				Privacy Policy		Adatvédelem

@{LANG_MORE_OPTIONS}				More options		További opciók
@{LANG_HOTEL_CATEGORY}				Hotel category		Hotel kategória

@{LANG_MONTH_01}						january		január
@{LANG_MONTH_02}						february	február
@{LANG_MONTH_03}						march		március
@{LANG_MONTH_04}						april		április
@{LANG_MONTH_05}						may			május
@{LANG_MONTH_06}						june		június
@{LANG_MONTH_07}						july		július
@{LANG_MONTH_08}						august		augusztus
@{LANG_MONTH_09}						september	szeptember
@{LANG_MONTH_10}						october		október
@{LANG_MONTH_11}						november	november
@{LANG_MONTH_12}						december	december

&{MONTHS_BY_NUMBER}
...		01=@{LANG_MONTH_01}
...		02=@{LANG_MONTH_02}
...		03=@{LANG_MONTH_03}
...		04=@{LANG_MONTH_04}
...		05=@{LANG_MONTH_05}
...		06=@{LANG_MONTH_06}
...		07=@{LANG_MONTH_07}
...		08=@{LANG_MONTH_08}
...		09=@{LANG_MONTH_09}
...		10=@{LANG_MONTH_10}
...		11=@{LANG_MONTH_11}
...		12=@{LANG_MONTH_12}

@{ISLANDS_EN}=		@{LANG_ISLAND_MAHE}[0]	@{LANG_ISLAND_LA_DIGUE}[0]	@{LANG_ISLAND_PRASLIN}[0]	@{LANG_ISLAND_CERF}[0]	@{LANG_ISLAND_ST_ANNE}[0]
@{ISLANDS_HU}=		@{LANG_ISLAND_MAHE}[1]	@{LANG_ISLAND_LA_DIGUE}[1]	@{LANG_ISLAND_PRASLIN}[1]	@{LANG_ISLAND_CERF}[1]	@{LANG_ISLAND_ST_ANNE}[1]

@{MEAL_PLANS_EN}=	@{LANG_MP_EP}[0]	@{LANG_MP_BB}[0]	@{LANG_MP_HB}[0]	@{LANG_MP_FB}[0]	@{LANG_MP_INC}[0]
@{MEAL_PLANS_HU}=	@{LANG_MP_EP}[1]	@{LANG_MP_BB}[1]	@{LANG_MP_HB}[1]	@{LANG_MP_FB}[1]	@{LANG_MP_INC}[1]

@{HOTEL_CATEGORY_EN}=	@{LANG_HC_HOTEL}[0]		@{LANG_HC_LUX_HOTEL}[0]		@{LANG_HC_APARTMENT}[0]		@{LANG_HC_VILLA}[0]		@{LANG_HC_GUEST_HOUSE}[0]	@{LANG_HC_PRIVATE_ROOM}[0]
@{HOTEL_CATEGORY_HU}=	@{LANG_HC_HOTEL}[1]		@{LANG_HC_LUX_HOTEL}[1]		@{LANG_HC_APARTMENT}[1]		@{LANG_HC_VILLA}[1]		@{LANG_HC_GUEST_HOUSE}[1]	@{LANG_HC_PRIVATE_ROOM}[1]


&{ISLANDS_BY_LANGUAGE_AND_NUMBER}
...		${0}=@{ISLANDS_EN}
...		${1}=@{ISLANDS_HU}
&{MEAL_PLANS_BY_LANGUAGE_AND_NUMBER}
...		${0}=@{MEAL_PLANS_EN}
...		${1}=@{MEAL_PLANS_HU}
&{HOTEL_CATEGORY_BY_LANGUAGE_AND_NUMBER}
...		${0}=@{HOTEL_CATEGORY_EN}
...		${1}=@{HOTEL_CATEGORY_HU}
