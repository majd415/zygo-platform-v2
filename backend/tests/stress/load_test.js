import http from 'k6/http';
import { check, sleep } from 'k6';
import { Trend, Rate } from 'k6/metrics';

// Custom metrics
const responseTime = new Trend('api_response_time');
const errorRate = new Rate('api_error_rate');

export const options = {
    stages: [
        { duration: '2m', target: 1 }, 
    ],
    thresholds: {
        http_req_duration: ['p(95)<500'], // 95% of requests must be below 500ms
        api_error_rate: ['rate<0.05'],    // Error rate must be less than 5%
    },
};

const BASE_URL = 'http://localhost:8081/taxiApp_backend/backend/public/index.php/api';

export default function () {
    // 1. Health Check (Main stressing target)
    let res = http.get(`${BASE_URL}/health`);
    
    check(res, {
        'status is 200': (r) => r.status === 200,
        'contains ok status': (r) => r.json('status') === 'ok',
    }) || errorRate.add(1);

    sleep(1);
}
