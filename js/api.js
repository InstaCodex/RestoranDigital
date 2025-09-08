/**
 * API Client untuk Restoran Digital
 * Menangani semua komunikasi dengan backend API
 */

class RestoranAPI {
    constructor() {
        this.baseUrl = 'api.php';
    }

    // Helper method untuk membuat request
    async request(action, method = 'GET', data = null) {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            }
        };

        if (data && (method === 'POST' || method === 'PUT')) {
            options.body = JSON.stringify(data);
        }

        try {
            const url = `${this.baseUrl}?action=${action}`;
            console.log('API Request:', { url, method, data });
            
            const response = await fetch(url, options);
            console.log('API Response status:', response.status);
            
            const responseText = await response.text();
            console.log('API Response text:', responseText);
            
            let result;
            try {
                result = JSON.parse(responseText);
            } catch (parseError) {
                console.error('JSON Parse Error:', parseError);
                console.error('Response was not JSON:', responseText);
                throw new Error('Server returned invalid JSON: ' + responseText.substring(0, 100));
            }
            
            if (!response.ok) {
                throw new Error(result.error || 'Request failed');
            }
            
            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    // ==================== KATEGORI ====================
    async getKategori() {
        return await this.request('get_kategori');
    }

    async createKategori(nama, deskripsi = '') {
        return await this.request('create_kategori', 'POST', { nama, deskripsi });
    }

    async updateKategori(id, nama, deskripsi = '') {
        return await this.request('update_kategori', 'PUT', { id, nama, deskripsi });
    }

    async deleteKategori(id) {
        return await this.request(`delete_kategori&id=${id}`, 'DELETE');
    }

    // ==================== MENU ====================
    async getMenu() {
        return await this.request('get_menu');
    }

    async createMenu(data) {
        return await this.request('create_menu', 'POST', data);
    }

    async updateMenu(data) {
        return await this.request('update_menu', 'PUT', data);
    }

    async deleteMenu(id) {
        return await this.request(`delete_menu&id=${id}`, 'DELETE');
    }

    async getMenuByKategori(kategori_id) {
        return await this.request(`get_menu_by_kategori&kategori_id=${kategori_id}`);
    }

    // ==================== MEJA ====================
    async getMeja() {
        return await this.request('get_meja');
    }

    async createMeja(data) {
        return await this.request('create_meja', 'POST', data);
    }

    async updateMeja(data) {
        return await this.request('update_meja', 'PUT', data);
    }

    async deleteMeja(id) {
        return await this.request(`delete_meja&id=${id}`, 'DELETE');
    }

    // ==================== PESANAN ====================
    async getPesanan() {
        return await this.request('get_pesanan');
    }

    async createPesanan(data) {
        return await this.request('create_pesanan', 'POST', data);
    }

    async updateStatusPesanan(id, status) {
        return await this.request('update_status_pesanan', 'PUT', { id, status });
    }

    async getPesananDetail(id) {
        return await this.request(`get_pesanan_detail&id=${id}`);
    }

    async selectTable(meja_id) {
        return await this.request('select_table', 'POST', { meja_id });
    }

    // ==================== AUTHENTICATION ====================
    async login(username, password) {
        return await this.request('login', 'POST', { username, password });
    }

    async logout() {
        return await this.request('logout', 'POST');
    }

    async checkAuth() {
        return await this.request('check_auth');
    }
}

// Global instance
const api = new RestoranAPI();

// Utility functions
function showNotification(message, type = 'success') {
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'warning' ? 'alert-warning' : 'alert-danger';
    const icon = type === 'success' ? 'fa-check-circle' : 
                type === 'warning' ? 'fa-exclamation-triangle' : 'fa-exclamation-circle';
    
    const alert = document.createElement('div');
    alert.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alert.innerHTML = `
        <i class="fas ${icon} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alert);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.parentNode.removeChild(alert);
        }
    }, 5000);
}

function showLoading(element, show = true) {
    if (show) {
        element.disabled = true;
        element.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
    } else {
        element.disabled = false;
        // Restore original content - this should be handled by the calling function
    }
}

function formatRupiah(angka) {
    return 'Rp ' + angka.toLocaleString('id-ID');
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}
