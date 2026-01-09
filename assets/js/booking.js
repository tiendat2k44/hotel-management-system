/**
 * Booking Module JavaScript
 */

class BookingModule {
    constructor() {
        this.baseUrl = document.body.dataset.baseUrl || '/';
    }
    
    /**
     * Check room availability
     */
    async checkAvailability(checkIn, checkOut) {
        try {
            const response = await fetch(this.baseUrl + 'api/check_room_availability.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `check_in=${encodeURIComponent(checkIn)}&check_out=${encodeURIComponent(checkOut)}`
            });
            
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            return await response.json();
        } catch (error) {
            console.error('Error:', error);
            showToast('Lỗi khi kiểm tra phòng', 'danger');
            return [];
        }
    }
    
    /**
     * Get room price
     */
    async getRoomPrice(roomId) {
        try {
            const response = await fetch(this.baseUrl + 'api/get_room_price.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `room_id=${encodeURIComponent(roomId)}`
            });
            
            const data = await response.json();
            return data.price || 0;
        } catch (error) {
            console.error('Error:', error);
            return 0;
        }
    }
    
    /**
     * Calculate total price
     */
    calculateTotal(price, nights, serviceTotal = 0) {
        return (price * nights) + serviceTotal;
    }
    
    /**
     * Update room list on date change
     */
    updateRoomList(checkInDate, checkOutDate) {
        const roomSelect = document.getElementById('room_id');
        if (!roomSelect) return;
        
        if (!checkInDate || !checkOutDate) {
            roomSelect.innerHTML = '<option value="">-- Chọn ngày trước --</option>';
            return;
        }
        
        this.checkAvailability(checkInDate, checkOutDate).then(rooms => {
            roomSelect.innerHTML = '<option value="">-- Chọn phòng --</option>';
            
            if (rooms.length === 0) {
                roomSelect.innerHTML += '<option disabled>Không có phòng trống</option>';
                return;
            }
            
            rooms.forEach(room => {
                const option = document.createElement('option');
                option.value = room.id;
                option.textContent = `${room.room_number} (${room.type_name} - ${formatCurrency(room.base_price)})`;
                roomSelect.appendChild(option);
            });
        });
    }
    
    /**
     * Update price display on room change
     */
    updatePriceDisplay(roomId, nights) {
        if (!roomId || !nights) return;
        
        this.getRoomPrice(roomId).then(price => {
            const total = price * nights;
            const totalDisplay = document.getElementById('room_total');
            if (totalDisplay) {
                totalDisplay.textContent = formatCurrency(total);
            }
        });
    }
    
    /**
     * Add service to booking
     */
    async addService(bookingId, serviceId, quantity = 1) {
        try {
            const response = await fetch(this.baseUrl + 'api/add_service.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `booking_id=${bookingId}&service_id=${serviceId}&quantity=${quantity}`
            });
            
            const data = await response.json();
            if (data.success) {
                showToast('Thêm dịch vụ thành công', 'success');
                return true;
            } else {
                showToast(data.message || 'Lỗi', 'danger');
                return false;
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Lỗi: ' + error.message, 'danger');
            return false;
        }
    }
    
    /**
     * Remove service from booking
     */
    async removeService(serviceUsageId) {
        try {
            const response = await fetch(this.baseUrl + 'api/remove_service.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${serviceUsageId}`
            });
            
            const data = await response.json();
            if (data.success) {
                showToast('Xóa dịch vụ thành công', 'success');
                return true;
            } else {
                showToast(data.message || 'Lỗi', 'danger');
                return false;
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Lỗi: ' + error.message, 'danger');
            return false;
        }
    }
}

// Initialize on load
const bookingModule = new BookingModule();

// Event listeners for booking form
document.addEventListener('DOMContentLoaded', function() {
    const checkInInput = document.getElementById('check_in');
    const checkOutInput = document.getElementById('check_out');
    const roomSelect = document.getElementById('room_id');
    
    if (checkInInput && checkOutInput) {
        checkInInput.addEventListener('change', function() {
            const nights = calculateNights(this.value, checkOutInput.value);
            if (nights > 0) {
                bookingModule.updateRoomList(this.value, checkOutInput.value);
            }
        });
        
        checkOutInput.addEventListener('change', function() {
            const nights = calculateNights(checkInInput.value, this.value);
            if (nights > 0) {
                bookingModule.updateRoomList(checkInInput.value, this.value);
            }
        });
    }
    
    if (roomSelect) {
        roomSelect.addEventListener('change', function() {
            const nights = calculateNights(checkInInput.value, checkOutInput.value);
            bookingModule.updatePriceDisplay(this.value, nights);
        });
    }
});

/**
 * Calculate nights between two dates
 */
function calculateNights(checkIn, checkOut) {
    if (!checkIn || !checkOut) return 0;
    
    const from = new Date(checkIn);
    const to = new Date(checkOut);
    const diff = to.getTime() - from.getTime();
    const nights = Math.ceil(diff / (1000 * 60 * 60 * 24));
    
    return nights > 0 ? nights : 0;
}

// Export for global use
window.bookingModule = bookingModule;
window.calculateNights = calculateNights;
