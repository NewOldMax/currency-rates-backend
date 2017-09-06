@pairs
Feature: Pairs

    Background: There are existing instances in the system
        Given I signed in as user
        And Pairs exist
        And Rates exist

    Scenario: I can view list of pairs
        When I want to get list of pairs
        Then I see list of pairs

    Scenario: I can view one pair
        When I want to get one pair
        Then I see requested pair

    Scenario: I can't view foreign pairs
        When I want to get foreign pair
        Then response code is 403

    Scenario: I can update my pair
        When I fill form field "base_currency" with "AUD"
        And I fill form field "target_currency" with "CAD"
        And I fill form field "value" with 100.31
        And I fill form field "duration" with "3 days"
        And Submit filled form
        Then I see field "base_currency" filled with "AUD"
        And I see field "target_currency" filled with "CAD"
        And I see field "value" filled with 100.31
        And I see field "duration" filled with "3 days"

    Scenario: I can't put incorrect data to pair
        When I fill form field "base_currency" with "dummy currency"
        And Submit filled form
        Then response code is 400
        When I fill form field "target_currency" with "dummy currency"
        And Submit filled form
        Then response code is 400
        When I fill form field "duration" with "1 year"
        And Submit filled form
        Then response code is 400

    Scenario: I can create new pair
        When I want to create new pair
        Then I see created pair

    Scenario: I can delete my pair
        When I want to delete my pair
        Then response code is 204
        When I want to get one pair
        Then response code is 404

    Scenario: I can get rates for pair
        When I want to get rates for pair
        Then I see rates