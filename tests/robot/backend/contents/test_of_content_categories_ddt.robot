*** Settings ***
Documentation			Backend Test for OTS Contents
Resource				../../resource/resource.robot
Resource				../resource/resource_basic_functions.robot
Resource				../resource/resource_helpers.robot
Resource				../resource/resource_navigations.robot
Resource				../resource/resource_templates.robot
Resource				../resource/resource_variables.robot
Suite Setup				Admin User Login And Navigate to Contents
Test Template			Content Category template
Default Tags			test_develop	contents

*** Test Cases ***
#               ${type}     ${en}           ${de}               ${hu}               ${ru}
First Case		New         CategoryTest1   CategorieTest1      KategóriaTest 1     RussiaTest 1
Mod Case		Edit		CategoryTest1  	CategorieTest2	    KategóriaTest 2	    RussiaTest 2
#Delete Case		Hotel Robot		Delete		Baby	0		3
#Delete Error	Hotel B		Delete		adult	0		3