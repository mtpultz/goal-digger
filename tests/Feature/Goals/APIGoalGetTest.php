<?php

test('Goal get Active endpoint should return a list of active goals for the current user', function () {
    $response = $this->get('/api/goals/active');

    $response->assertStatus(200);
});
