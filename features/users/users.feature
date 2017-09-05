@users
Feature: Users

    Background: There are existing instances in the system
        Given I signed in as user

    Scenario: I can log out
        When I want to logout
        And I want to get access to server
        Then response code is 401
