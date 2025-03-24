Feature: Logs API

  Scenario: Count logs after processing
    Given the log table is empty
    And the log file is empty
    And I have generated 10 logs into the file
    And I have processed the log file
    When I request "/logs/count"
    Then the response status code should be 200
    And the JSON should contain "counter" equal to 10

  Scenario: Truncate logs
    Given the log table is empty
    And the log file is empty
    And I have generated 5 logs into the file
    And I have processed the log file
    When I send a DELETE request to "/logs"
    Then the response status code should be 204
    And the log table should be empty
