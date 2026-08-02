Feature: Kubernetes probes

  Scenario: Liveness probe answers without touching any dependency
    When I send a GET request to "/healthz"
    Then the response code is 200
    And the response content is:
    """
    {"status":"ok"}
    """

  Scenario: Readiness probe reports the database is reachable
    When I send a GET request to "/readyz"
    Then the response code is 200
    And the response content is:
    """
    {"status":"ok"}
    """
