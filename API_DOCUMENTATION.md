# API Documentation - Hotel Management System

## Giới Thiệu

Tài liệu này mô tả chi tiết các API endpoints của hệ thống quản lý khách sạn. Tất cả các API đều sử dụng POST method và trả về JSON response.

## Base URL
```
http://localhost/hotel-management-system/api/
```

---

## 1. Check Room Availability

### Endpoint
```
POST /api/check_room_availability.php
```

### Mô Tả
Kiểm tra phòng trống trong khoảng thời gian xác định

### Request Parameters
| Parameter | Type | Required | Mô Tả |
|-----------|------|----------|-------|
| check_in | string | Yes | Ngày check-in (YYYY-MM-DD) |
| check_out | string | Yes | Ngày check-out (YYYY-MM-DD) |
| booking_id | integer | No | ID booking (nếu edit booking, loại trừ booking này) |

### Request Example
```javascript
const data = {
    check_in: '2025-01-15',
    check_out: '2025-01-18',
    booking_id: null
};

fetch('/api/check_room_availability.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
})
.then(response => response.json())
.then(result => console.log(result));
```

### Response Success (Status 200)
```json
{
    "status": "success",
    "message": "Available rooms found",
    "data": [
        {
            "id": 1,
            "room_number": "101",
            "room_type": "Deluxe",
            "floor": 1,
            "price": 500000,
            "max_guests": 2,
            "status": "Available"
        },
        {
            "id": 2,
            "room_number": "102",
            "room_type": "Standard",
            "floor": 1,
            "price": 300000,
            "max_guests": 2,
            "status": "Available"
        }
    ]
}
```

### Response Error (Status 200 with error)
```json
{
    "status": "error",
    "message": "Check-out date must be after check-in date"
}
```

### Response - No Rooms Available (Status 200)
```json
{
    "status": "success",
    "message": "No available rooms found",
    "data": []
}
```

### Error Cases
| Error | Mô Tả | HTTP Status |
|-------|-------|------------|
| Missing parameters | Thiếu check_in hoặc check_out | 200 (JSON error) |
| Invalid date format | Ngày không đúng định dạng YYYY-MM-DD | 200 (JSON error) |
| Check-out before check-in | Check-out sớm hơn check-in | 200 (JSON error) |

### Status Codes
- **200 OK**: Request thành công (bao gồm cả error cases trả JSON)
- **405 Method Not Allowed**: Sử dụng method không phải POST
- **500 Internal Server Error**: Lỗi server

---

## 2. Get Room Price

### Endpoint
```
POST /api/get_room_price.php
```

### Mô Tả
Lấy giá phòng theo room_id

### Request Parameters
| Parameter | Type | Required | Mô Tả |
|-----------|------|----------|-------|
| room_id | integer | Yes | ID phòng |

### Request Example
```javascript
const data = { room_id: 1 };

fetch('/api/get_room_price.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
})
.then(response => response.json())
.then(result => console.log(result));
```

### Response Success
```json
{
    "status": "success",
    "data": {
        "room_id": 1,
        "room_number": "101",
        "room_type": "Deluxe",
        "price": 500000,
        "currency": "VND"
    }
}
```

### Response Error
```json
{
    "status": "error",
    "message": "Room not found"
}
```

---

## 3. Calculate Room Total

### Endpoint
```
POST /api/calculate_room_total.php
```

### Mô Tả
Tính tổng tiền phòng dựa trên số đêm ở

### Request Parameters
| Parameter | Type | Required | Mô Tả |
|-----------|------|----------|-------|
| room_id | integer | Yes | ID phòng |
| check_in | string | Yes | Ngày check-in (YYYY-MM-DD) |
| check_out | string | Yes | Ngày check-out (YYYY-MM-DD) |
| include_vat | boolean | No | Có tính VAT không (default: false) |

### Request Example
```javascript
const data = {
    room_id: 1,
    check_in: '2025-01-15',
    check_out: '2025-01-18',
    include_vat: true
};

fetch('/api/calculate_room_total.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
})
.then(response => response.json())
.then(result => console.log(result));
```

### Response Success
```json
{
    "status": "success",
    "data": {
        "room_id": 1,
        "room_number": "101",
        "price_per_night": 500000,
        "nights": 3,
        "subtotal": 1500000,
        "vat": 150000,
        "total": 1650000,
        "currency": "VND"
    }
}
```

### Response Error
```json
{
    "status": "error",
    "message": "Invalid date range"
}
```

---

## 4. Add Service to Booking

### Endpoint
```
POST /api/add_service.php
```

### Mô Tả
Thêm dịch vụ vào booking

### Request Parameters
| Parameter | Type | Required | Mô Tả |
|-----------|------|----------|-------|
| booking_id | integer | Yes | ID booking |
| service_id | integer | Yes | ID dịch vụ |
| quantity | integer | Yes | Số lượng (≥ 1) |
| notes | string | No | Ghi chú thêm |

### Request Example
```javascript
const data = {
    booking_id: 5,
    service_id: 2,
    quantity: 2,
    notes: "Massage bổ sung 1 phút"
};

fetch('/api/add_service.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
})
.then(response => response.json())
.then(result => console.log(result));
```

### Response Success
```json
{
    "status": "success",
    "message": "Service added successfully",
    "data": {
        "service_usage_id": 12,
        "service_name": "Massage",
        "quantity": 2,
        "price_per_unit": 200000,
        "total": 400000,
        "added_at": "2025-01-15 10:30:00"
    }
}
```

### Response Error
```json
{
    "status": "error",
    "message": "Booking not found or invalid quantity"
}
```

---

## 5. Remove Service from Booking

### Endpoint
```
POST /api/remove_service.php
```

### Mô Tả
Xóa dịch vụ khỏi booking

### Request Parameters
| Parameter | Type | Required | Mô Tả |
|-----------|------|----------|-------|
| service_usage_id | integer | Yes | ID dịch vụ đã thêm (từ service_usage table) |

### Request Example
```javascript
const data = { service_usage_id: 12 };

fetch('/api/remove_service.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
})
.then(response => response.json())
.then(result => console.log(result));
```

### Response Success
```json
{
    "status": "success",
    "message": "Service removed successfully",
    "data": {
        "service_usage_id": 12,
        "refund_amount": 400000
    }
}
```

### Response Error
```json
{
    "status": "error",
    "message": "Service usage not found"
}
```

---

## 6. Get Booking Details

### Endpoint
```
POST /api/get_booking_details.php
```

### Mô Tả
Lấy chi tiết booking kèm tất cả dịch vụ

### Request Parameters
| Parameter | Type | Required | Mô Tả |
|-----------|------|----------|-------|
| booking_id | integer | Yes | ID booking |

### Request Example
```javascript
const data = { booking_id: 5 };

fetch('/api/get_booking_details.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
})
.then(response => response.json())
.then(result => console.log(result));
```

### Response Success
```json
{
    "status": "success",
    "data": {
        "booking": {
            "id": 5,
            "code": "BK20250115001",
            "customer_name": "Nguyễn Văn A",
            "room_number": "101",
            "check_in": "2025-01-15",
            "check_out": "2025-01-18",
            "nights": 3,
            "adults": 2,
            "children": 1,
            "status": "confirmed",
            "created_at": "2025-01-10 14:00:00"
        },
        "room": {
            "id": 1,
            "room_number": "101",
            "room_type": "Deluxe",
            "price_per_night": 500000
        },
        "services": [
            {
                "id": 12,
                "service_name": "Massage",
                "quantity": 2,
                "unit": "lần",
                "price_per_unit": 200000,
                "total": 400000
            },
            {
                "id": 13,
                "service_name": "Breakfast",
                "quantity": 3,
                "unit": "phần",
                "price_per_unit": 100000,
                "total": 300000
            }
        ],
        "pricing": {
            "room_total": 1500000,
            "services_total": 700000,
            "subtotal": 2200000,
            "vat": 220000,
            "total": 2420000,
            "deposit": 500000,
            "remaining": 1920000,
            "currency": "VND"
        }
    }
}
```

### Response Error
```json
{
    "status": "error",
    "message": "Booking not found"
}
```

---

## 7. Update Booking Status

### Endpoint
```
POST /api/update_booking_status.php
```

### Mô Tả
Cập nhật trạng thái booking

### Request Parameters
| Parameter | Type | Required | Mô Tả |
|-----------|------|----------|-------|
| booking_id | integer | Yes | ID booking |
| status | string | Yes | Trạng thái mới (pending, confirmed, checked_in, checked_out, cancelled) |
| notes | string | No | Ghi chú |

### Request Example
```javascript
const data = {
    booking_id: 5,
    status: 'checked_in',
    notes: 'Khách đến lúc 14:30'
};

fetch('/api/update_booking_status.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
})
.then(response => response.json())
.then(result => console.log(result));
```

### Response Success
```json
{
    "status": "success",
    "message": "Booking status updated successfully",
    "data": {
        "booking_id": 5,
        "old_status": "confirmed",
        "new_status": "checked_in",
        "updated_at": "2025-01-15 14:35:00"
    }
}
```

### Status Values
| Status | Mô Tả |
|--------|-------|
| pending | Chờ xác nhận |
| confirmed | Đã xác nhận |
| checked_in | Đã check-in |
| checked_out | Đã check-out |
| cancelled | Đã hủy |

---

## 8. Generate Invoice

### Endpoint
```
POST /api/generate_invoice.php
```

### Mô Tả
Tạo hóa đơn cho booking (thường khi check-out)

### Request Parameters
| Parameter | Type | Required | Mô Tả |
|-----------|------|----------|-------|
| booking_id | integer | Yes | ID booking |
| format | string | No | Định dạng (json, pdf - default: json) |

### Request Example
```javascript
const data = {
    booking_id: 5,
    format: 'json'
};

fetch('/api/generate_invoice.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
})
.then(response => response.json())
.then(result => console.log(result));
```

### Response Success
```json
{
    "status": "success",
    "message": "Invoice generated successfully",
    "data": {
        "invoice_id": 3,
        "invoice_code": "INV20250115001",
        "booking_code": "BK20250115001",
        "customer_name": "Nguyễn Văn A",
        "check_in": "2025-01-15",
        "check_out": "2025-01-18",
        "room_info": {
            "number": "101",
            "type": "Deluxe",
            "nights": 3,
            "price_per_night": 500000,
            "room_total": 1500000
        },
        "services": [
            {
                "name": "Massage",
                "quantity": 2,
                "unit": "lần",
                "price": 200000,
                "total": 400000
            },
            {
                "name": "Breakfast",
                "quantity": 3,
                "unit": "phần",
                "price": 100000,
                "total": 300000
            }
        ],
        "financial": {
            "subtotal": 2200000,
            "vat_rate": 10,
            "vat_amount": 220000,
            "total": 2420000,
            "deposit_paid": 500000,
            "remaining": 1920000,
            "currency": "VND"
        },
        "created_at": "2025-01-18 11:00:00",
        "pdf_url": "/invoices/INV20250115001.pdf"
    }
}
```

---

## Request Headers

Tất cả API requests nên include các headers sau:

```
Content-Type: application/json
Accept: application/json
```

### Optional Headers
```
Authorization: Bearer <token>  (nếu có authentication token)
X-Requested-With: XMLHttpRequest
```

---

## Response Format

Tất cả API responses đều có format chung:

### Success Response
```json
{
    "status": "success",
    "message": "Operation successful",
    "data": { /* data */ }
}
```

### Error Response
```json
{
    "status": "error",
    "message": "Error description",
    "code": "ERROR_CODE"
}
```

### Status Values
| Status | HTTP Code | Mô Tả |
|--------|-----------|-------|
| success | 200 | Thành công |
| error | 200 / 400 / 500 | Lỗi |

---

## Error Codes

| Error Code | HTTP Status | Mô Tả |
|-----------|-------------|-------|
| INVALID_REQUEST | 400 | Request không hợp lệ |
| MISSING_PARAMETER | 400 | Thiếu parameter |
| UNAUTHORIZED | 401 | Không được phép |
| FORBIDDEN | 403 | Bị cấm |
| NOT_FOUND | 404 | Không tìm thấy |
| INVALID_DATA | 422 | Dữ liệu không hợp lệ |
| SERVER_ERROR | 500 | Lỗi server |

---

## Rate Limiting

- Không có rate limiting hiện tại
- Tương lai sẽ implement: 100 requests/phút mỗi user

---

## Authentication

- Hiện tại: Session-based (kiểm tra login)
- Tương lai: JWT Token support

---

## Examples

### Example 1: Check Availability & Calculate Price

```javascript
// 1. Check room availability
async function findRooms(checkIn, checkOut) {
    const response = await fetch('/api/check_room_availability.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ check_in: checkIn, check_out: checkOut })
    });
    return await response.json();
}

// 2. Get room price
async function getRoomInfo(roomId) {
    const response = await fetch('/api/get_room_price.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ room_id: roomId })
    });
    return await response.json();
}

// 3. Calculate total
async function calculateTotal(roomId, checkIn, checkOut) {
    const response = await fetch('/api/calculate_room_total.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            room_id: roomId,
            check_in: checkIn,
            check_out: checkOut,
            include_vat: true
        })
    });
    return await response.json();
}

// Usage
const rooms = await findRooms('2025-01-15', '2025-01-18');
if (rooms.status === 'success' && rooms.data.length > 0) {
    const roomId = rooms.data[0].id;
    const total = await calculateTotal(roomId, '2025-01-15', '2025-01-18');
    console.log('Total:', total.data.total);
}
```

---

**Phiên bản API:** 1.0.0  
**Cập nhật:** Tháng 12/2025  
**Hỗ trợ:** support@hotel.local
