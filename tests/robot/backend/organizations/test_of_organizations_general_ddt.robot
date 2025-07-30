*** Settings ***
Documentation			Backend Test for OTS Organizations
Resource				../../resource/resource.robot
Resource				../resource/resource_basic_functions.robot
Resource				../resource/resource_helpers.robot
Resource				../resource/resource_navigations.robot
Resource				../resource/resource_templates.robot
Resource				../resource/resource_variables.robot
Suite Setup				Admin User Login And Navigate to Organization
Test Template			General template
Default Tags			test_develop
#Test Teardown			Close Browser

*** Test Cases ***
#				Hotel Name		Parent Organizations		Active	Short description	Long description
First Case		Hotel Robot;;;Робот отель	${EMPTY}		true	Short description;Kurze Beschreibung;Rövid leírás;Краткое описание	You're completely sure of the math?;Te teljesen biztos vagy a matekban?;Sind Sie der Mathematik völlig sicher?;Вы совершенно уверены в математике?