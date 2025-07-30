*** Settings ***
Documentation			Backend Test for OTS Contents
Resource				../../resource/resource.robot
Resource				../resource/resource_basic_functions.robot
Resource				../resource/resource_helpers.robot
Resource				../resource/resource_navigations.robot
Resource				../resource/resource_templates.robot
Resource				../resource/resource_variables.robot
Suite Setup				Admin User Login And Navigate to Contents
Test Template			Content template
Default Tags			test_develop	contents

*** Test Cases ***
#               ${type}     ${title}	${lead}		${content}		${meta_description} 	${meta_keyword}     ${meta_title}       ${status}
First Case		New         New Title	    New Lead	New Content	    New Meta description     New Keyword    New meta title      published
Mod Case		Edit		New Title   	New Lead2	New Content2	New Meta description2    New Keyword2   New meta title2     draft
#Delete Case		Hotel Robot		Delete		Baby	0		3
#Delete Error	Hotel B		Delete		adult	0		3