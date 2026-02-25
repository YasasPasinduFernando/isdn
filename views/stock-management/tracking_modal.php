<!-- ============================================
     TRANSFER TRACKING MODAL
     Separate component for tracking transfer progress
     ============================================ -->

<!-- Tracking Modal Overlay -->
<div id="tracking-modal" class="hidden fixed inset-0 z-50" style="background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(4px);">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
            
            <!-- Modal Header -->
            <div class="sticky top-0 bg-gradient-to-r from-blue-600 to-purple-600 px-6 py-4 z-10">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-white flex items-center">
                            <i class="fas fa-route mr-3"></i>
                            Track Transfer Request
                        </h3>
                        <p id="tracking-transfer-number" class="mono text-sm text-blue-100 mt-1"></p>
                    </div>
                    <button id="tracking-modal-close" class="h-10 w-10 rounded-full hover:bg-white hover:bg-opacity-20 flex items-center justify-center transition">
                        <i class="fas fa-times text-white"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto" style="max-height: calc(90vh - 140px);">
                
                <!-- Transfer Summary Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                        <div class="text-xs text-blue-600 font-medium mb-1">From</div>
                        <div id="tracking-source-rdc" class="text-sm font-bold text-blue-900"></div>
                    </div>
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200">
                        <div class="text-xs text-purple-600 font-medium mb-1">To</div>
                        <div id="tracking-destination-rdc" class="text-sm font-bold text-purple-900"></div>
                    </div>
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                        <div class="text-xs text-green-600 font-medium mb-1">Items</div>
                        <div id="tracking-total-items" class="text-sm font-bold text-green-900"></div>
                    </div>
                    <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4 border border-orange-200">
                        <div class="text-xs text-orange-600 font-medium mb-1">Current Status</div>
                        <div id="tracking-current-status" class="text-sm font-bold text-orange-900"></div>
                    </div>
                </div>

                <!-- Progress Timeline -->
                <div class="mb-8">
                    <h4 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-stream mr-2 text-blue-600"></i>
                        Transfer Progress Timeline
                    </h4>

                    <!-- Visual Progress Bar -->
                    <div class="mb-8">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-gray-500">Progress</span>
                            <span id="progress-percentage" class="text-xs font-bold text-blue-600">0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                            <div id="progress-bar" class="h-3 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full transition-all duration-500" style="width: 0%"></div>
                        </div>
                        <div class="flex justify-between mt-2 text-xs text-gray-500">
                            <span>Requested</span>
                            <span>Approved</span>
                            <span>Delivered</span>
                        </div>
                    </div>

                    <!-- Timeline Steps -->
                    <div id="timeline-container" class="space-y-4">
                        <!-- Timeline items will be inserted here dynamically -->
                    </div>
                </div>

                <!-- Status Change History Table -->
                <div class="bg-gray-50 rounded-xl border border-gray-200 overflow-hidden">
                    <div class="bg-gray-100 px-6 py-3 border-b border-gray-200">
                        <h4 class="text-sm font-bold text-gray-900 flex items-center">
                            <i class="fas fa-history mr-2 text-gray-600"></i>
                            Detailed Status History
                        </h4>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date & Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Previous Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">New Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Changed By</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                                </tr>
                            </thead>
                            <tbody id="status-history-tbody" class="bg-white divide-y divide-gray-200">
                                <!-- Status history rows will be inserted here -->
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="sticky bottom-0 bg-gray-50 border-t border-gray-200 px-6 py-4 flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    <i class="fas fa-info-circle mr-1"></i>
                    Last updated: <span id="last-updated-time" class="font-semibold">Just now</span>
                </div>
                <button id="tracking-modal-close-btn" class="px-6 py-2 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tracking Modal Styles -->
<style>
    /* Timeline styles */
    .timeline-item {
        position: relative;
        padding-left: 40px;
        opacity: 0;
        animation: slideInLeft 0.4s ease-out forwards;
    }

    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: 12px;
        top: 32px;
        bottom: -32px;
        width: 2px;
        background: linear-gradient(to bottom, #3b82f6, #e5e7eb);
    }

    .timeline-item:last-child::before {
        display: none;
    }

    .timeline-icon {
        position: absolute;
        left: 0;
        top: 0;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        box-shadow: 0 0 0 4px white;
    }

    .timeline-icon.completed {
        background: linear-gradient(135deg, #10b981, #059669);
        animation: pulse 2s infinite;
    }

    .timeline-icon.current {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        animation: pulse 2s infinite;
    }

    .timeline-icon.pending {
        background: #e5e7eb;
    }

    .timeline-content {
        animation-delay: 0.1s;
    }
</style>

<!-- Tracking Modal JavaScript -->
<script>
// Real data will be fetched from the server. The large dummy `statusHistoryData` was removed.

// Helper: fetch transfer tracking from server
function fetchTransferTracking(transfer) {
    const params = transfer.transfer_id ? `transfer_id=${transfer.transfer_id}` : `transfer_number=${encodeURIComponent(transfer.transfer_number)}`;
    // Adjust the base path if your app is hosted under a different root
    const url = '/controllers/stock-management/TrackingTransferProductsController.php?action=get_tracking&' + params;
    return fetch(url, { credentials: 'same-origin' }).then(res => res.json());
}

// Status configuration
const statusConfig = {
    'CLERK_REQUESTED': {
        label: 'Clerk Requested',
        icon: 'fa-file-alt',
        color: 'yellow',
        description: 'Transfer request created by clerk'
    },
    'PENDING': {
        label: 'Pending Approval',
        icon: 'fa-clock',
        color: 'blue',
        description: 'Waiting for source RDC manager approval'
    },
    'APPROVED': {
        label: 'Approved',
        icon: 'fa-check-circle',
        color: 'green',
        description: 'Approved by source RDC, ready for dispatch'
    },
    'REJECTED': {
        label: 'Rejected',
        icon: 'fa-times-circle',
        color: 'red',
        description: 'Request rejected by source RDC manager'
    },
    'CANCELLED': {
        label: 'Cancelled',
        icon: 'fa-ban',
        color: 'gray',
        description: 'Request cancelled'
    },
    'RECEIVED': {
        label: 'Received',
        icon: 'fa-box-open',
        color: 'purple',
        description: 'Stock received at destination RDC'
    }
};

// Open tracking modal
function openTrackingModal(transfer) {
    const modal = document.getElementById('tracking-modal');

    // Show modal immediately with basic info while we load full details
    document.getElementById('tracking-transfer-number').textContent = transfer.transfer_number || '...';
    document.getElementById('tracking-source-rdc').textContent = transfer.source_rdc || '...';
    document.getElementById('tracking-destination-rdc').textContent = transfer.destination_rdc || '...';
    document.getElementById('tracking-total-items').textContent = (transfer.product_count ? (transfer.product_count + ' products (' + transfer.total_items + ' units)') : 'Loading...');
    document.getElementById('tracking-current-status').textContent = (transfer.status || '').replace(/_/g, ' ');

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    // Fetch authoritative data from server
    fetchTransferTracking(transfer).then(resp => {
        if (!resp || resp.success === false) {
            console.error('Tracking load error', resp && resp.message);
            document.getElementById('tracking-current-status').textContent = 'Error loading';
            return;
        }

        const t = resp.transfer;
        const statusHistory = resp.status_history || [];        

        // Fill transfer info from server
        document.getElementById('tracking-transfer-number').textContent = t.transfer_number;
        document.getElementById('tracking-source-rdc').textContent = t.source_rdc;
        document.getElementById('tracking-destination-rdc').textContent = t.destination_rdc;
        document.getElementById('tracking-total-items').textContent = t.product_count + ' products (' + t.total_items + ' units)';
        document.getElementById('tracking-current-status').textContent = t.status.replace(/_/g, ' ');

        // Calculate and set progress
        const statusOrder = ['CLERK_REQUESTED', 'PENDING', 'APPROVED', 'RECEIVED'];
        const currentIndex = statusOrder.indexOf(t.status);
        const progressPercent = currentIndex >= 0 ? ((currentIndex + 1) / statusOrder.length) * 100 : 0;
        document.getElementById('progress-percentage').textContent = Math.round(progressPercent) + '%';
        document.getElementById('progress-bar').style.width = progressPercent + '%';

        // Build timeline & history
        buildTimeline(t.status, statusHistory);
        buildStatusHistoryTable(statusHistory);

        // Update last updated time
        if (statusHistory.length > 0) {
            const lastLog = statusHistory[statusHistory.length - 1];
            document.getElementById('last-updated-time').textContent = formatDateTime(lastLog.changed_date);
        } else {
            document.getElementById('last-updated-time').textContent = 'Just now';
        }

    }).catch(err => {
        console.error('Failed to fetch tracking data', err);
        document.getElementById('tracking-current-status').textContent = 'Failed to load';
    });
}

// Close tracking modal
function closeTrackingModal() {
    const modal = document.getElementById('tracking-modal');
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Build timeline
function buildTimeline(currentStatus, statusHistory) {
    const container = document.getElementById('timeline-container');
    container.innerHTML = '';

    const baseStatuses = ['CLERK_REQUESTED', 'PENDING', 'APPROVED', 'RECEIVED'];
    const terminalStatuses = ['REJECTED', 'CANCELLED'];

    // Derive statuses present in history (ordered, unique)
    const historyStatuses = [...new Set(statusHistory.map(s => s.new_status))];

    // If a terminal status exists in the history, build the timeline up to that terminal status
    const terminalInHistory = historyStatuses.find(s => terminalStatuses.includes(s));
    let timelineSteps = [];
    if (terminalInHistory) {
        const idx = historyStatuses.indexOf(terminalInHistory);
        timelineSteps = historyStatuses.slice(0, idx + 1);
        // Ensure the first step is at least CLERK_REQUESTED for clarity
        if (timelineSteps[0] !== 'CLERK_REQUESTED') timelineSteps.unshift('CLERK_REQUESTED');
    } else {
        // No terminal — show full expected flow with placeholders for future steps
        timelineSteps = baseStatuses;
    }

    const currentIndex = timelineSteps.indexOf(currentStatus);

    timelineSteps.forEach((status, index) => {
        const config = statusConfig[status] || { label: status.replace(/_/g,' '), icon: 'fa-circle', color: 'gray', description: '' };
        const isCompleted = index < currentIndex || (index === currentIndex && terminalStatuses.includes(status) === false && statusHistory.some(h=>h.new_status===status));
        const isCurrent = index === currentIndex;

        // Find matching log entry (history ordered asc)
        const logEntry = statusHistory.find(log => log.new_status === status);

        let statusClass = 'pending';
        if (isCompleted && !isCurrent) {
            statusClass = 'completed';
        } else if (isCurrent) {
            statusClass = terminalStatuses.includes(status) ? 'completed' : 'current';
        }

        const timelineItem = document.createElement('div');
        timelineItem.className = 'timeline-item';
        timelineItem.style.animationDelay = (index * 0.1) + 's';

        timelineItem.innerHTML = `
            <div class="timeline-icon ${statusClass}">
                <i class="fas ${config.icon} text-white text-xs"></i>
            </div>
            <div class="timeline-content bg-white rounded-lg border-2 ${
                isCompleted ? 'border-' + config.color + '-200 bg-' + config.color + '-50' : 'border-gray-200'
            } p-4">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <div class="font-bold text-gray-900 flex items-center">
                            <span class="text-sm">${config.label}</span>
                            ${isCurrent && !terminalStatuses.includes(status) ? '<span class="ml-2 px-2 py-0.5 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">Current</span>' : ''}
                            ${isCompleted && !isCurrent ? '<span class="ml-2 text-green-600 text-xs"><i class="fas fa-check"></i></span>' : ''}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">${config.description}</div>
                    </div>
                </div>
                ${logEntry ? `
                    <div class="mt-3 pt-3 border-t border-gray-200 grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-xs text-gray-500">Changed By</div>
                            <div class="text-sm font-semibold text-gray-900">${logEntry.changed_by_name}</div>
                            <div class="text-xs text-gray-500">${logEntry.change_by_role}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Date & Time</div>
                            <div class="text-sm font-semibold text-gray-900">${formatDate(logEntry.changed_date)}</div>
                            <div class="text-xs text-gray-500">${formatTime(logEntry.changed_date)}</div>
                        </div>
                    </div>
                ` : `
                    <div class="mt-3 text-xs text-gray-400 italic">
                        <i class="fas fa-hourglass-half mr-1"></i>
                        Awaiting this step
                    </div>
                `}
            </div>
        `;

        container.appendChild(timelineItem);
    });

    // Update visual progress bar based on timelineSteps and currentIndex
    const progressEl = document.getElementById('progress-bar');
    const percentEl = document.getElementById('progress-percentage');
    if (progressEl && percentEl) {
        const idx = Math.max(0, Math.min(currentIndex, timelineSteps.length - 1));
        const progressPercent = timelineSteps.length > 0 && currentIndex >= 0 ? Math.round(((idx + 1) / timelineSteps.length) * 100) : 0;
        progressEl.style.width = progressPercent + '%';
        percentEl.textContent = progressPercent + '%';
    }
}

// Build status history table
function buildStatusHistoryTable(statusHistory) {
    const tbody = document.getElementById('status-history-tbody');
    tbody.innerHTML = '';
    
    // Reverse to show latest first
    const reversedHistory = [...statusHistory].reverse();
    
    reversedHistory.forEach(log => {
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50';
        
        const statusColors = {
            'CLERK_REQUESTED': 'bg-yellow-100 text-yellow-700',
            'PENDING': 'bg-blue-100 text-blue-700',
            'APPROVED': 'bg-green-100 text-green-700',
            'REJECTED': 'bg-red-100 text-red-700',
            'CANCELLED': 'bg-gray-100 text-gray-700',
            'RECEIVED': 'bg-purple-100 text-purple-700'
        };
        
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${formatDate(log.changed_date)}</div>
                <div class="text-xs text-gray-500">${formatTime(log.changed_date)}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                ${log.previous_status ? 
                    `<span class="px-2 py-1 text-xs font-semibold rounded ${statusColors[log.previous_status] || 'bg-gray-100 text-gray-700'}">
                        ${log.previous_status.replace(/_/g, ' ')}
                    </span>` 
                    : '<span class="text-xs text-gray-400">Initial</span>'}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 text-xs font-semibold rounded ${statusColors[log.new_status] || 'bg-gray-100 text-gray-700'}">
                    ${log.new_status.replace(/_/g, ' ')}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">${log.changed_by_name}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-600">${log.change_by_role}</div>
            </td>
        `;
        
        tbody.appendChild(row);
    });
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

// Format time
function formatTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
}

// Format date time
function formatDateTime(dateString) {
    return formatDate(dateString) + ' at ' + formatTime(dateString);
}

// Event listeners
document.getElementById('tracking-modal-close')?.addEventListener('click', closeTrackingModal);
document.getElementById('tracking-modal-close-btn')?.addEventListener('click', closeTrackingModal);

// Close on backdrop click
document.getElementById('tracking-modal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeTrackingModal();
    }
});
</script>