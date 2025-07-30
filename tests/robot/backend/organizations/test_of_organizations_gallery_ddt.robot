*** Settings ***
Documentation			Backend Test for OTS Organizations
Resource				../../resource/resource.robot
Resource				../resource/resource_basic_functions.robot
Resource				../resource/resource_helpers.robot
Resource				../resource/resource_navigations.robot
Resource				../resource/resource_templates.robot
Resource				../resource/resource_variables.robot
Suite Setup				Admin User Login And Navigate to Organization
Default Tags			test_develop
Test Template			Image Template
#Test Teardown			Close Browser

*** Test Cases ***
Upload First File		Hotel A		Upload		c:\\upload\\frantz.jpg
Set Description			Hotel A		Edit	last	Anna and Adrien at the lake in Germany;Adrien Anna und die See in Deutschland;Anna és Adrien a tóparton Németországban;Adrien Анна и озеро в Германии		true
Delete Image Case		Hotel A		Delete		last
#Property Case			Hotel A		Properties	Eng;Ger;;Rus	frontend