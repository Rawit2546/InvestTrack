<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Investment Tracker - Blue Edition</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600;700&display=swap"
    rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <style>
    body {
      font-family: 'Noto Sans Thai', sans-serif;
      background-color: #f8fafc;
    }

    .loader {
      border-top-color: #2563eb;
      animation: spinner 1.5s linear infinite;
    }

    @keyframes spinner {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }

    .swal2-html-container {
      margin: 1em 0 0 !important;
      overflow: visible !important;
    }

    .modal-blur {
      backdrop-filter: blur(8px);
      background-color: rgba(15, 23, 42, 0.4);
    }

    .scale-in {
      animation: scaleIn 0.2s ease-out;
    }

    @keyframes scaleIn {
      from {
        transform: scale(0.95);
        opacity: 0;
      }

      to {
        transform: scale(1);
        opacity: 1;
      }
    }

    .partner-info-box {
      transition: all 0.3s ease;
      max-height: 0;
      opacity: 0;
      overflow: hidden;
    }

    .partner-info-box.active {
      max-height: 500px;
      opacity: 1;
      margin-bottom: 1rem;
    }
  </style>
</head>

<body class="min-h-screen text-slate-800">

  <!-- Mock API Proxy for Canvas Preview -->
  <script>
    if (typeof google === 'undefined') {
      const mockData = {
        getDashboardData: () => ({
          summary: { totalInvest: 750000, totalDiv: 82000, totalExp: 15000, netBalance: 817000 },
          businesses: [
            { id: 'BIZ-1', name: 'ธุรกิจฟาร์มโซลาร์เซลล์', investment: 500000, accumulatedDiv: 60000, contractDate: '2023-05-10', dividendRate: '12% ต่อปี', payDate: 'ทุกวันที่ 1', duration: '5 ปี', note: 'โครงการเฟส 1' },
            { id: 'BIZ-2', name: 'ร้านสะดวกซื้อ 24 ชม.', investment: 250000, accumulatedDiv: 22000, contractDate: '2023-11-20', dividendRate: 'ปันผลรายเดือน', payDate: 'ทุกวันที่ 30', duration: '3 ปี', note: 'ทำเลหน้ามหาวิทยาลัย' }
          ]
        }),
        getPartnersByBiz: (id) => ([
          { id: 'PN-1', name: 'คุณสมชาย (Partner)', amount: 100000, div: '2%', note: 'หุ้นส่วนหลัก' },
          { id: 'PN-2', name: 'คุณมานี (Co-invest)', amount: 50000, div: '1%', note: '' }
        ]),
        updatePartner: (id, d) => ({ success: true }),
        deletePartner: (id) => ({ success: true }),
        addBusiness: (d) => ({ success: true }),
        addTransaction: (d) => ({ success: true }),
        deleteBusiness: (id) => ({ success: true })
      };

      const createMockProxy = (sHandler, fHandler) => {
        return new Proxy({}, {
          get: (target, prop) => {
            if (prop === 'withSuccessHandler') return (sh) => createMockProxy(sh, fHandler);
            if (prop === 'withFailureHandler') return (fh) => createMockProxy(sHandler, fh);
            return (...args) => {
              setTimeout(() => {
                if (mockData[prop]) {
                  try {
                    const result = mockData[prop](...args);
                    if (sHandler) sHandler(result);
                  } catch (e) { if (fHandler) fHandler(e); }
                }
              }, 500);
            };
          }
        });
      };
      window.google = { script: { run: createMockProxy() } };
    }
  </script>

  <div id="app" class="pb-12">

    <nav class="bg-white border-b sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between h-16 items-center">
        <div class="flex items-center gap-2">
          <i data-lucide="trending-up" class="text-blue-600 w-8 h-8"></i>
          <span class="text-xl font-bold tracking-tight">Invest<span class="text-blue-600">Track</span></span>
        </div>
        <div class="flex items-center gap-6">
          <div class="flex items-center gap-2">
             <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold">
                {{ substr(Auth::user()->name, 0, 1) }}
             </div>
             <span class="text-slate-700 font-medium">{{ Auth::user()->name }}</span>
          </div>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex items-center gap-2 text-slate-400 hover:text-red-500 transition-colors font-medium text-sm">
              <span>ออกจากระบบ</span>
              <i data-lucide="log-out" class="w-5 h-5"></i>
            </button>
          </form>
        </div>
      </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 text-slate-800">
      <!-- Loading -->
      <div id="loading" class="flex flex-col items-center py-20">
        <div class="loader rounded-full border-4 h-12 w-12 mb-4 border-slate-100"></div>
        <p class="text-slate-400 animate-pulse text-sm">กำลังดึงข้อมูลจากฐานข้อมูล...</p>
      </div>

      <div id="dashboard-content">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 transition-all hover:shadow-md">
            <div class="flex items-center gap-3 mb-2 text-slate-400">
              <i data-lucide="wallet" class="w-4 h-4"></i>
              <span class="text-xs font-bold uppercase tracking-wider">เงินลงทุนรวม</span>
            </div>
            <p id="stat-invest" class="text-2xl font-bold tracking-tight">฿0.00</p>
          </div>
          <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 transition-all hover:shadow-md">
            <div class="flex items-center gap-3 mb-2 text-blue-500">
              <i data-lucide="trending-up" class="w-4 h-4"></i>
              <span class="text-xs font-bold uppercase tracking-wider text-slate-400">ปันผลรวม</span>
            </div>
            <p id="stat-dividend" class="text-2xl font-bold text-green-600 tracking-tight">฿0.00</p>
          </div>
          <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 transition-all hover:shadow-md">
            <div class="flex items-center gap-3 mb-2 text-red-500">
              <i data-lucide="trending-down" class="w-4 h-4"></i>
              <span class="text-xs font-bold uppercase tracking-wider text-slate-400">รายจ่ายรวม</span>
            </div>
            <p id="stat-expense" class="text-2xl font-bold text-red-500 tracking-tight">฿0.00</p>
          </div>
          <div class="bg-blue-600 p-6 rounded-2xl shadow-xl text-white transition-all hover:bg-blue-700">
            <div class="flex items-center gap-3 mb-2">
              <i data-lucide="landmark" class="w-4 h-4 text-blue-200"></i>
              <span class="text-xs font-bold uppercase tracking-wider text-blue-100">ยอดสุทธิในพอร์ต</span>
            </div>
            <p id="stat-net" class="text-2xl font-bold tracking-tight">฿0.00</p>
          </div>
        </div>

        <div class="flex flex-wrap gap-4 mb-8">
          <button onclick="openModal('modal-business')"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-2xl flex items-center gap-2 shadow-lg transition-all active:scale-95">
            <i data-lucide="plus-circle" class="w-5 h-5"></i> เพิ่มการลงทุนใหม่
          </button>
          <button onclick="openModal('modal-transaction')"
            class="bg-white border-2 border-slate-100 hover:bg-slate-50 text-slate-600 px-6 py-3 rounded-2xl flex items-center gap-2 transition-all active:scale-95 font-medium">
            <i data-lucide="file-text" class="w-5 h-5"></i> บันทึกธุรกรรม
          </button>
          <button onclick="exportData()"
            class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-2xl flex items-center gap-2 shadow-lg transition-all active:scale-95">
            <i data-lucide="sheet" class="w-5 h-5"></i> Export to Sheet
          </button>
        </div>

        <!-- Business Table -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
          <div class="p-6 border-b border-slate-50 flex justify-between items-center">
            <h2 class="text-lg font-bold flex items-center gap-2 text-slate-700">
              <i data-lucide="list" class="w-5 h-5 text-blue-600"></i> รายการธุรกิจที่ลงทุน
            </h2>
            <span class="bg-slate-50 text-slate-400 text-xs px-3 py-1 rounded-full font-bold" id="biz-count">0
              รายการ</span>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-left">
              <thead class="bg-slate-50/50 text-slate-500 text-xs uppercase tracking-widest">
                <tr>
                  <th class="px-8 py-4">ชื่อธุรกิจ / วันทำสัญญา</th>
                  <th class="px-6 py-4 text-center">เงินลงทุน</th>
                  <th class="px-6 py-4 text-center">ปันผลสะสม</th>
                  <th class="px-8 py-4 text-right">จัดการ</th>
                </tr>
              </thead>
              <tbody id="business-table-body" class="divide-y divide-slate-50">
                <!-- Table Content -->
              </tbody>
            </table>
          </div>
        </div>

        <!-- Transaction Table -->
        <div class="mt-8 bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
          <div class="p-6 border-b border-slate-50 flex justify-between items-center">
            <h2 class="text-lg font-bold flex items-center gap-2 text-slate-700">
              <i data-lucide="history" class="w-5 h-5 text-purple-600"></i> ประวัติธุรกรรม
            </h2>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-left">
              <thead class="bg-slate-50/50 text-slate-500 text-xs uppercase tracking-widest">
                <tr>
                  <th class="px-6 py-4">วันที่</th>
                   <th class="px-6 py-4">ประเภท</th>
                  <th class="px-6 py-4">ธุรกิจ</th>
                  <th class="px-6 py-4">หุ้นส่วน</th>
                  <th class="px-6 py-4 text-center">จำนวนเงิน</th>
                  <th class="px-6 py-4 text-right">หมายเหตุ</th>
                  <th class="px-6 py-4 text-center w-24">จัดการ</th>
                </tr>
              </thead>
              <tbody id="transaction-table-body" class="divide-y divide-slate-50">
                <!-- Data -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>

    <!-- Modal: Add Business -->
    <div id="modal-business" class="hidden fixed inset-0 modal-blur z-[60] flex items-center justify-center p-4">
      <div
        class="bg-white rounded-3xl w-full max-w-3xl max-h-[90vh] overflow-y-auto shadow-2xl scale-in text-slate-800">
        <div class="p-8 border-b flex justify-between items-center sticky top-0 bg-white/80 backdrop-blur-md z-10">
          <h3 class="text-xl font-bold">บันทึกข้อมูลการลงทุน</h3>
          <button onclick="closeModal('modal-business')" class="p-2 hover:bg-slate-50 rounded-full transition-colors"><i
              data-lucide="x" class="text-slate-400"></i></button>
        </div>
        <form id="form-business" class="p-8 space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
              <label class="block text-xs font-bold uppercase text-slate-400 mb-2">ชื่อธุรกิจที่ลงทุน</label>
              <input type="text" name="name" required
                class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-3 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
            <div>
              <label class="block text-xs font-bold uppercase text-slate-400 mb-2">วันทำสัญญา</label>
              <input type="date" name="contractDate" required
                class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-3 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
            <div>
              <label class="block text-xs font-bold uppercase text-slate-400 mb-2">จำนวนเงินลงทุน (บาท)</label>
              <input type="number" name="investment" required
                class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-3 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
            <div>
              <label class="block text-xs font-bold uppercase text-slate-400 mb-2">เปอร์เซ็นปันผล / อัตรา</label>
              <input type="text" name="dividendRate" placeholder="เช่น 5% ต่อเดือน"
                class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-3 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
            <div>
              <label class="block text-xs font-bold uppercase text-slate-400 mb-2">รอบวันที่จ่ายปันผล</label>
              <input type="text" name="payDate" placeholder="เช่น ทุกวันที่ 5 ของเดือน"
                class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-3 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
            <div class="md:col-span-2">
              <label class="block text-xs font-bold uppercase text-slate-400 mb-2">ระยะเวลาสัญญา</label>
              <input type="text" name="duration" placeholder="เช่น 1 ปี, 12 เดือน หรือระบุวันที่สิ้นสุด"
                class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-3 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
          </div>

          <div class="border-t border-slate-100 pt-6">
            <div class="flex justify-between items-center mb-6">
              <h4 class="font-bold text-blue-600 flex items-center gap-2">
                <i data-lucide="users" class="w-5 h-5"></i> ผู้ลงทุนร่วม (Partners)
              </h4>
              <button type="button" onclick="addPartnerRow()" class="text-sm bg-blue-50 text-blue-600 px-3 py-1.5 rounded-lg hover:bg-blue-100 transition-colors">
                + เพิ่มผู้ร่วมลงทุน
              </button>

            </div>
            <div id="partner-container" class="space-y-4">
              <!-- Rows -->
            </div>
          </div>

          <button type="submit"
            class="w-full bg-blue-600 text-white py-5 rounded-2xl font-bold shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all active:scale-95">บันทึกข้อมูลการลงทุน</button>
        </form>
      </div>
    </div>

    <!-- Modal: View/Edit Business Details -->
    <div id="modal-biz-detail" class="hidden fixed inset-0 modal-blur z-[60] flex items-center justify-center p-4">
      <div class="bg-white rounded-3xl w-full max-w-2xl p-8 shadow-2xl scale-in overflow-y-auto max-h-[90vh] text-slate-800">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-xl font-bold flex items-center gap-2">
            <i data-lucide="file-text" class="text-blue-600"></i> รายละเอียดการลงทุน
          </h3>
          <button onclick="closeModal('modal-biz-detail')" class="bg-slate-100 p-2 rounded-full hover:bg-slate-200 transition-colors">
            <i data-lucide="x" class="w-5 h-5 text-slate-500"></i>
          </button>
        </div>

        <form id="form-edit-business" class="space-y-6">
          <input type="hidden" id="edit-biz-id">
          
          <!-- View Mode vs Edit Mode Logic handled by JS -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 p-6 rounded-2xl border border-slate-100">
            <div class="md:col-span-2">
              <label class="block text-xs font-bold uppercase text-slate-400 mb-1">ชื่อธุรกิจ</label>
              <input type="text" id="view-name" class="biz-input w-full bg-transparent border-0 font-bold text-xl text-slate-800 p-0 focus:ring-0" readonly>
            </div>
            <div>
              <label class="block text-xs font-bold uppercase text-slate-400 mb-1">เงินลงทุน</label>
              <input type="number" id="view-investment" class="biz-input w-full bg-transparent border-0 font-semibold text-slate-700 p-0 focus:ring-0" readonly>
            </div>
            <div>
              <label class="block text-xs font-bold uppercase text-slate-400 mb-1">วันที่เริ่มสัญญา</label>
              <input type="date" id="view-contractDate" class="biz-input w-full bg-transparent border-0 font-semibold text-slate-700 p-0 focus:ring-0" readonly>
            </div>
             <div>
              <label class="block text-xs font-bold uppercase text-slate-400 mb-1">ปันผล</label>
              <input type="text" id="view-dividendRate" class="biz-input w-full bg-transparent border-0 font-semibold text-slate-700 p-0 focus:ring-0" readonly>
            </div>
            <div>
              <label class="block text-xs font-bold uppercase text-slate-400 mb-1">รอบจ่าย</label>
              <input type="text" id="view-payDate" class="biz-input w-full bg-transparent border-0 font-semibold text-slate-700 p-0 focus:ring-0" readonly>
            </div>
            <div class="md:col-span-2">
              <label class="block text-xs font-bold uppercase text-slate-400 mb-1">ระยะเวลา</label>
              <input type="text" id="view-duration" class="biz-input w-full bg-transparent border-0 font-semibold text-slate-700 p-0 focus:ring-0" readonly>
            </div>
            <div class="md:col-span-2">
              <label class="block text-xs font-bold uppercase text-slate-400 mb-1">หมายเหตุ</label>
              <textarea id="view-note" rows="2" class="biz-input w-full bg-transparent border-0 text-slate-600 p-0 resize-none focus:ring-0" readonly></textarea>
            </div>
          </div>

          <!-- Partners Section in View/Edit -->
          <div>
            <div class="flex justify-between items-center mb-4">
               <h4 class="font-bold text-slate-700 flex items-center gap-2">
                <i data-lucide="users" class="w-4 h-4"></i> ผู้ลงทุนร่วม
              </h4>
              <button type="button" id="btn-add-partner-edit" onclick="addPartnerRowEdit()" class="hidden text-xs bg-blue-50 text-blue-600 px-3 py-1.5 rounded-lg hover:bg-blue-100">
                + เพิ่ม
              </button>
            </div>
            <div id="edit-partner-container" class="space-y-3">
              <!-- Partners injected here -->
            </div>
          </div>

          <div class="flex gap-3 pt-4 border-t border-slate-100">
            <button type="button" id="btn-edit-toggle" onclick="toggleEditMode()" class="flex-1 bg-slate-100 text-slate-600 py-3 rounded-xl font-bold hover:bg-slate-200 transition-all">
              แก้ไขข้อมูล
            </button>
            <button type="submit" id="btn-save-edit" class="hidden flex-1 bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all">
              บันทึกการเปลี่ยนแปลง
            </button>
             <button type="button" id="btn-cancel-edit" onclick="toggleEditMode()" class="hidden bg-white border border-slate-200 text-slate-500 px-6 py-3 rounded-xl font-bold hover:bg-slate-50">
              ยกเลิก
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal: View/Edit Business Details -->
    <div id="modal-biz-detail" class="hidden fixed inset-0 modal-blur z-[60] flex items-center justify-center p-4">
      <div class="bg-white rounded-3xl w-full max-w-2xl p-8 shadow-2xl scale-in overflow-y-auto max-h-[90vh] text-slate-800">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-xl font-bold flex items-center gap-2">
            <i data-lucide="file-text" class="text-blue-600"></i> รายละเอียดการลงทุน
          </h3>
          <button onclick="closeModal('modal-biz-detail')" class="bg-slate-100 p-2 rounded-full hover:bg-slate-200 transition-colors">
            <i data-lucide="x" class="w-5 h-5 text-slate-500"></i>
          </button>
        </div>

        <form id="form-edit-business" class="space-y-6">
          <input type="hidden" id="edit-biz-id">
          
          <!-- View Mode vs Edit Mode Logic handled by JS -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 p-6 rounded-2xl border border-slate-100">
            <div class="md:col-span-2">
              <label class="block text-xs font-bold uppercase text-slate-400 mb-1">ชื่อธุรกิจ</label>
              <input type="text" id="view-name" class="biz-input w-full bg-transparent border-0 font-bold text-xl text-slate-800 p-0 focus:ring-0" readonly>
            </div>
            <div>
              <label class="block text-xs font-bold uppercase text-slate-400 mb-1">เงินลงทุน</label>
              <input type="number" id="view-investment" class="biz-input w-full bg-transparent border-0 font-semibold text-slate-700 p-0 focus:ring-0" readonly>
            </div>
            <div>
              <label class="block text-xs font-bold uppercase text-slate-400 mb-1">วันที่เริ่มสัญญา</label>
              <input type="date" id="view-contractDate" class="biz-input w-full bg-transparent border-0 font-semibold text-slate-700 p-0 focus:ring-0" readonly>
            </div>
             <div>
              <label class="block text-xs font-bold uppercase text-slate-400 mb-1">ปันผล</label>
              <input type="text" id="view-dividendRate" class="biz-input w-full bg-transparent border-0 font-semibold text-slate-700 p-0 focus:ring-0" readonly>
            </div>
            <div>
              <label class="block text-xs font-bold uppercase text-slate-400 mb-1">รอบจ่าย</label>
              <input type="text" id="view-payDate" class="biz-input w-full bg-transparent border-0 font-semibold text-slate-700 p-0 focus:ring-0" readonly>
            </div>
            <div class="md:col-span-2">
              <label class="block text-xs font-bold uppercase text-slate-400 mb-1">ระยะเวลา</label>
              <input type="text" id="view-duration" class="biz-input w-full bg-transparent border-0 font-semibold text-slate-700 p-0 focus:ring-0" readonly>
            </div>
            <div class="md:col-span-2">
              <label class="block text-xs font-bold uppercase text-slate-400 mb-1">หมายเหตุ</label>
              <textarea id="view-note" rows="2" class="biz-input w-full bg-transparent border-0 text-slate-600 p-0 resize-none focus:ring-0" readonly></textarea>
            </div>
          </div>

          <!-- Partners Section in View/Edit -->
          <div>
            <div class="flex justify-between items-center mb-4">
               <h4 class="font-bold text-slate-700 flex items-center gap-2">
                <i data-lucide="users" class="w-4 h-4"></i> ผู้ลงทุนร่วม
              </h4>
              <button type="button" id="btn-add-partner-edit" onclick="addPartnerRowEdit()" class="hidden text-xs bg-blue-50 text-blue-600 px-3 py-1.5 rounded-lg hover:bg-blue-100">
                + เพิ่ม
              </button>
            </div>
            <div id="edit-partner-container" class="space-y-3">
              <!-- Partners injected here -->
            </div>
          </div>

          <div class="flex gap-3 pt-4 border-t border-slate-100">
            <button type="button" id="btn-edit-toggle" onclick="toggleEditMode()" class="flex-1 bg-slate-100 text-slate-600 py-3 rounded-xl font-bold hover:bg-slate-200 transition-all">
              แก้ไขข้อมูล
            </button>
            <button type="submit" id="btn-save-edit" class="hidden flex-1 bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all">
              บันทึกการเปลี่ยนแปลง
            </button>
             <button type="button" id="btn-cancel-edit" onclick="toggleEditMode()" class="hidden bg-white border border-slate-200 text-slate-500 px-6 py-3 rounded-xl font-bold hover:bg-slate-50">
              ยกเลิก
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal: Add Transaction -->
    <div id="modal-transaction" class="hidden fixed inset-0 modal-blur z-[60] flex items-center justify-center p-4">
      <div
        class="bg-white rounded-3xl w-full max-w-md p-8 shadow-2xl scale-in overflow-y-auto max-h-[90vh] text-slate-800">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-xl font-bold">บันทึกธุรกรรมการเงิน</h3>
          <button onclick="closeModal('modal-transaction')"><i data-lucide="x" class="text-slate-400"></i></button>
        </div>
        <form id="form-transaction" class="space-y-4">
          <div>
            <label class="block text-xs font-bold uppercase text-slate-400 mb-2">เลือกธุรกิจ</label>
            <select name="businessId" id="biz-dropdown" required onchange="handleTransactionModalChange()"
              class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-3 outline-none focus:ring-2 focus:ring-blue-500 transition-all"></select>
          </div>

          <div>
            <label class="block text-xs font-bold uppercase text-slate-400 mb-2">ประเภทธุรกรรม</label>
            <select name="type" id="tx-type-dropdown" onchange="handleTransactionModalChange()"
              class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-3 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
              <option value="ปันผล">ปันผล (พอร์ตหลัก) (+)</option>
              <option value="ปันผลหุ้นส่วน">ปันผลให้ผู้ร่วมลงทุน (-)</option>
              <option value="รายจ่าย">รายจ่าย (-)</option>
            </select>
          </div>

          <!-- Partner Selector (Visible only when 'ปันผลหุ้นส่วน' is selected) -->
          <div id="partner-selector-container" class="hidden">
            <label
              class="block text-xs font-bold uppercase text-blue-600 mb-2 tracking-widest">เลือกหุ้นส่วนที่จะรับเงิน</label>
            <select name="partnerId" id="partner-dropdown"
              class="w-full bg-blue-50 border-0 rounded-2xl px-5 py-3 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
              <option value="">-- เลือกหุ้นส่วน --</option>
            </select>
          </div>

          <!-- Partner Info Display (Dynamic) -->
          <div id="partner-info-display" class="partner-info-box">
            <label
              class="block text-[10px] font-bold uppercase text-blue-400 mb-2 tracking-widest">รายละเอียดการลงทุน</label>
            <div id="partner-list-content"
              class="space-y-2 bg-blue-50 p-4 rounded-2xl border border-blue-100 max-h-40 overflow-y-auto">
              <!-- Content injected by JS -->
            </div>
          </div>

          <div class="grid grid-cols-1 gap-4">
            <div>
              <label class="block text-xs font-bold uppercase text-slate-400 mb-2">จำนวนเงินปันผล/รายจ่าย (บาท)</label>
              <input type="number" name="amount" required
                class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-3 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold uppercase text-slate-400 mb-2">วันที่ธุรกรรม</label>
            <input type="date" name="date" id="tx-date-now" required
              class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-3 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
          </div>

          <div>
            <label class="block text-xs font-bold uppercase text-slate-400 mb-2">หมายเหตุ</label>
            <textarea name="note" rows="2" placeholder="ระบุรายละเอียดธุรกรรม (ถ้ามี)"
              class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-3 outline-none focus:ring-2 focus:ring-blue-500 transition-all resize-none"></textarea>
          </div>

          <button type="submit"
            class="w-full bg-blue-600 text-white py-4 rounded-2xl font-bold shadow-lg mt-4 transition-all active:scale-95">บันทึกธุรกรรม</button>
        </form>
      </div>
    </div>
  </div>

  <script>
    let appData = { businesses: [], summary: {} };

    window.onload = function () {
      if (typeof lucide !== 'undefined') lucide.createIcons();
      document.getElementById('tx-date-now').valueAsDate = new Date();
      // Auto load data (Auth handled by Laravel)
      fetchData();
    };

    function exportData() {
      // Trigger download directly via API
      window.location.href = '/api/export';
    }



    function showDashboard() {
      document.getElementById('dashboard-content').classList.remove('hidden');
      fetchData();
    }


    async function fetchData() {
      const loader = document.getElementById('loading');
      const content = document.getElementById('dashboard-content');
      loader.classList.remove('hidden');
      content.classList.add('hidden');

      try {
        // Run specific sequential requests to avoid php built-in server deadlock
        const summaryRes = await fetch('/api/dashboard/summary');
        if (!summaryRes.ok) throw new Error('Summary API failed');
        const summaryData = await summaryRes.json();

        const businessesRes = await fetch('/api/businesses');
        if (!businessesRes.ok) throw new Error('Businesses API failed');
        const businessesData = await businessesRes.json();

        // Map API response to UI structure
        // Map API response to UI structure
        // Net Balance = Total Dividends - Total Expenses (User Requirement)
        const net = parseFloat(summaryData.total_dividends) - parseFloat(summaryData.total_expenses);

        appData = {
          summary: {
            totalInvest: summaryData.total_investment,
            totalDiv: summaryData.total_dividends,
            totalExp: summaryData.total_expenses,
            netBalance: net
          },
          businesses: businessesData
        };
        
        updateUI();
        fetchTransactions();
        loader.classList.add('hidden');
        content.classList.remove('hidden');
      } catch (error) {
        console.error('Error:', error);
        loader.classList.add('hidden');
        Swal.fire({
          icon: 'error',
          title: 'เกิดข้อผิดพลาด',
          text: 'ไม่สามารถเชื่อมต่อกับฐานข้อมูลได้ (' + error.message + ')',
          confirmButtonText: 'ลองใหม่',
          confirmButtonColor: '#2563eb',
          customClass: { popup: 'rounded-3xl' }
        });
      }
    }

    function updateUI() {
      const { summary = {}, businesses = [] } = appData;
      document.getElementById('stat-invest').innerText = formatCur(summary.totalInvest);
      document.getElementById('stat-dividend').innerText = formatCur(summary.totalDiv);
      document.getElementById('stat-expense').innerText = formatCur(summary.totalExp);
      document.getElementById('stat-net').innerText = formatCur(summary.netBalance);
      document.getElementById('biz-count').innerText = `${businesses.length} รายการ`;

      const tbody = document.getElementById('business-table-body');
      tbody.innerHTML = '';
      const dropdown = document.getElementById('biz-dropdown');
      dropdown.innerHTML = '<option value="">-- เลือกธุรกิจที่ต้องการบันทึก --</option>';

      businesses.forEach(biz => {
        const row = document.createElement('tr');
        row.className = "hover:bg-slate-50 transition-colors group";
        // Safe ID handling (convert to string if number)
        const safeId = String(biz.id).replace(/['"\\]/g, '');
        // Safe Name handling
        const safeName = String(biz.name).replace(/['"\\]/g, '');

        row.innerHTML = `
                    <td class="px-8 py-6">
                        <div onclick="viewBizDetails(String('${safeId}'))" class="font-bold text-slate-800 text-base mb-1 cursor-pointer hover:text-blue-600 transition-colors">${biz.name}</div>
                        <div class="text-[10px] text-slate-400 uppercase tracking-widest flex items-center gap-1">
                            <i data-lucide="calendar" class="w-3 h-3"></i> เริ่มเมื่อ: ${biz.contractDate ? new Date(biz.contractDate).toLocaleDateString('th-TH') : '-'}
                        </div>
                    </td>
                    <td class="px-6 py-6 text-center font-semibold text-slate-700">${formatCur(biz.investment)}</td>
                    <td class="px-6 py-6 text-center text-green-600 font-bold">${formatCur(biz.accumulatedDiv)}</td>
                    <td class="px-8 py-6 text-right">
                        <div class="flex justify-end gap-2 opacity-100 md:opacity-0 group-hover:opacity-100 transition-all">
                            <button onclick="confirmDelete('${safeId}', '${safeName}')" class="p-3 bg-slate-50 text-slate-300 hover:bg-red-50 hover:text-red-600 rounded-xl transition-all" title="ลบข้อมูล">
                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </td>
                `;
        tbody.appendChild(row);
        dropdown.add(new Option(biz.name, biz.id));
      });
      lucide.createIcons();
    }

    // --- View & Edit Details Logic ---
    let currentBizDetails = null;

    async function viewBizDetails(id) {
      Swal.fire({ title: 'กำลังโหลด...', didOpen: () => Swal.showLoading(), allowOutsideClick: false, background: 'transparent', backdrop: 'rgba(0,0,0,0.5)' });
      try {
        const res = await fetch(`/api/businesses/${id}`);
        if (!res.ok) throw new Error('Fetch failed');
        currentBizDetails = await res.json();
        
        // Populate Data
        document.getElementById('edit-biz-id').value = currentBizDetails.id;
        document.getElementById('view-name').value = currentBizDetails.name;
        document.getElementById('view-investment').value = currentBizDetails.investment;
        document.getElementById('view-contractDate').value = currentBizDetails.contract_date;
        document.getElementById('view-dividendRate').value = currentBizDetails.dividend_rate || '';
        document.getElementById('view-payDate').value = currentBizDetails.pay_date || '';
        document.getElementById('view-duration').value = currentBizDetails.duration || '';
        document.getElementById('view-note').value = currentBizDetails.note || '';

        // Populate Partners
        const pContainer = document.getElementById('edit-partner-container');
        pContainer.innerHTML = '';
        if (currentBizDetails.partners && currentBizDetails.partners.length > 0) {
            currentBizDetails.partners.forEach(p => addPartnerRowEdit(p));
        } else {
            pContainer.innerHTML = '<div class="text-center text-slate-400 text-sm py-4">ไม่มีผู้ร่วมลงทุน</div>';
        }

        // Reset to View Mode
        setEditMode(false);
        openModal('modal-biz-detail');
        Swal.close();
      } catch (err) {
        console.error(err);
        Swal.fire('Error', 'ไม่สามารถโหลดข้อมูลได้', 'error');
      }
    }

    function setEditMode(isEdit) {
        const form = document.getElementById('form-edit-business');
        const inputs = form.querySelectorAll('.biz-input');
        
        inputs.forEach(input => {
            if (isEdit) {
                input.classList.remove('bg-transparent', 'border-0', 'p-0');
                input.classList.add('bg-white', 'border', 'border-slate-200', 'px-4', 'py-2', 'rounded-xl', 'shadow-sm');
                input.readOnly = false;
            } else {
                 input.classList.add('bg-transparent', 'border-0', 'p-0');
                input.classList.remove('bg-white', 'border', 'border-slate-200', 'px-4', 'py-2', 'rounded-xl', 'shadow-sm');
                input.readOnly = true;
            }
        });

        const pInputs = document.querySelectorAll('.edit-p-input');
        pInputs.forEach(i => i.disabled = !isEdit);
        
        const pDeleteBtns = document.querySelectorAll('.edit-p-del');
        pDeleteBtns.forEach(b => isEdit ? b.classList.remove('hidden') : b.classList.add('hidden'));

        if (isEdit) {
            document.getElementById('btn-edit-toggle').classList.add('hidden');
            document.getElementById('btn-save-edit').classList.remove('hidden');
            document.getElementById('btn-cancel-edit').classList.remove('hidden');
            document.getElementById('btn-add-partner-edit').classList.remove('hidden');
        } else {
            document.getElementById('btn-edit-toggle').classList.remove('hidden');
            document.getElementById('btn-save-edit').classList.add('hidden');
            document.getElementById('btn-cancel-edit').classList.add('hidden');
            document.getElementById('btn-add-partner-edit').classList.add('hidden');
        }
    }

    function toggleEditMode() {
        const isCurrentlyEdit = !document.getElementById('btn-save-edit').classList.contains('hidden');
        if (isCurrentlyEdit) {
            // Cancel -> Reset data
            viewBizDetails(document.getElementById('edit-biz-id').value);
        } else {
            setEditMode(true);
        }
    }

    function addPartnerRowEdit(data = null) {
        const container = document.getElementById('edit-partner-container');
        // Clear "No partners" message if adding first row
        if (container.querySelector('.text-center')) container.innerHTML = '';

        const rowId = 'edit-p-row-' + Date.now();
        const div = document.createElement('div');
        div.className = "grid grid-cols-3 gap-2 items-center bg-slate-50 p-3 rounded-xl";
        div.innerHTML = `
            <input type="hidden" class="edit-p-id" value="${data ? data.id : ''}">
            <input type="text" class="edit-p-input w-full bg-white border border-slate-200 rounded-lg px-2 py-1 text-sm" value="${data ? data.name : ''}" placeholder="ชื่อ" disabled required>
            <input type="number" class="edit-p-input w-full bg-white border border-slate-200 rounded-lg px-2 py-1 text-sm" value="${data ? data.amount : ''}" placeholder="บาท" disabled required>
            <div class="relative">
                <input type="text" class="edit-p-input w-full bg-white border border-slate-200 rounded-lg px-2 py-1 text-sm" value="${data ? data.div_rate || '' : ''}" placeholder="%" disabled>
                <button type="button" onclick="this.closest('.grid').remove()" class="edit-p-del hidden absolute -right-2 -top-2 bg-red-100 text-red-500 rounded-full p-1 hover:bg-red-200"><i data-lucide="x" class="w-3 h-3"></i></button>
            </div>
        `;
        container.appendChild(div);
        lucide.createIcons();
        
        // If adding new row in edit mode, ensure it's enabled
        if (!document.getElementById('btn-save-edit').classList.contains('hidden')) {
             const newInputs = div.querySelectorAll('.edit-p-input');
             newInputs.forEach(i => i.disabled = false);
             div.querySelector('.edit-p-del').classList.remove('hidden');
        }
    }

    document.getElementById('form-edit-business').onsubmit = async function(e) {
        e.preventDefault();
        const id = document.getElementById('edit-biz-id').value;
        
        const partners = [];
        document.querySelectorAll('#edit-partner-container > div').forEach(row => {
            const pid = row.querySelector('.edit-p-id').value;
            const inputs = row.querySelectorAll('.edit-p-input');
            if (inputs[0].value) {
                partners.push({
                    id: pid || null,
                    name: inputs[0].value,
                    amount: inputs[1].value,
                    div: inputs[2].value
                });
            }
        });

        const data = {
            name: document.getElementById('view-name').value,
            investment: document.getElementById('view-investment').value,
            contractDate: document.getElementById('view-contractDate').value,
            dividendRate: document.getElementById('view-dividendRate').value,
            payDate: document.getElementById('view-payDate').value,
            duration: document.getElementById('view-duration').value,
            note: document.getElementById('view-note').value,
            partners: partners
        };

        Swal.fire({ title: 'กำลังบันทึก...', didOpen: () => Swal.showLoading() });

        try {
            const res = await fetch(`/api/businesses/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            });

            if(!res.ok) {
                 const err = await res.json();
                 throw new Error(err.message || 'Update failed');
            }

            await Swal.fire({ icon: 'success', title: 'เรียบร้อย', text: 'อัปเดตข้อมูลสำเร็จ', timer: 1500, showConfirmButton: false });
            closeModal('modal-biz-detail');
            fetchData();
        } catch (err) {
            Swal.fire('ผิดพลาด', err.message, 'error');
        }
    };

    // Handle dynamic info display in transaction modal
    async function handleTransactionModalChange() {
      const bizId = document.getElementById('biz-dropdown').value;
      const txType = document.getElementById('tx-type-dropdown').value;
      const infoBox = document.getElementById('partner-info-display');
      const content = document.getElementById('partner-list-content');

      const partnerContainer = document.getElementById('partner-selector-container');
      const partnerDropdown = document.getElementById('partner-dropdown');

      if (txType === 'ปันผลหุ้นส่วน' && bizId) {
        content.innerHTML = '<div class="flex items-center gap-2 text-xs text-blue-400"><div class="loader h-3 w-3 rounded-full border-2"></div> กำลังดึงข้อมูลหุ้นส่วน...</div>';
        infoBox.classList.add('active');
        partnerContainer.classList.remove('hidden');

        try {
            const res = await fetch(`/api/businesses/${bizId}`);
            if (!res.ok) throw new Error('Fetch failed');
            const data = await res.json();
            const partners = data.partners || [];

            if (partners.length > 0) {
                // Populate Partner Dropdown
                partnerDropdown.innerHTML = '<option value="">-- เลือกหุ้นส่วน --</option>';
                partners.forEach(p => {
                    const opt = new Option(p.name, p.id);
                    partnerDropdown.add(opt);
                });

                // Populate Info List
                content.innerHTML = partners.map(p => `
                    <div class="flex justify-between items-center bg-white p-2 rounded-xl text-xs shadow-sm border border-blue-100">
                        <div>
                            <span class="font-bold text-slate-700">${p.name}</span>
                            <div class="text-[10px] text-slate-400">อัตรา: ${p.div || '-'}</div>
                        </div>
                        <div class="text-right">
                            <span class="font-bold text-blue-600">${formatCur(p.amount)}</span>
                            <div class="text-[10px] text-slate-400 uppercase tracking-tighter">ยอดลงทุน</div>
                        </div>
                    </div>
                `).join('');
            } else {
                partnerDropdown.innerHTML = '<option value="">-- ไม่มีผู้ร่วมลงทุน --</option>';
                content.innerHTML = '<div class="text-xs text-slate-400 py-2 text-center">ธุรกิจนี้ไม่มีผู้ร่วมลงทุน</div>';
            }

        } catch (err) {
            console.error(err);
            content.innerHTML = '<div class="text-xs text-red-400 py-2 text-center">ไม่สามารถดึงข้อมูลได้</div>';
        }
      } else {
        infoBox.classList.remove('active');
        partnerContainer.classList.add('hidden');
        content.innerHTML = '';
        partnerDropdown.innerHTML = '<option value="">-- เลือกหุ้นส่วน --</option>';
      }
    }

    function confirmDelete(id, name) {
      Swal.fire({
        title: 'ลบข้อมูลธุรกิจ?',
        text: `คุณต้องการลบ "${name}" ใช่หรือไม่? (ข้อมูลธุรกรรมและผู้ร่วมลงทุนทั้งหมดจะหายไป)`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'ยืนยันลบ',
        customClass: { popup: 'rounded-3xl' }
      }).then(async (r) => {
        if (r.isConfirmed) {
            Swal.fire({ title: 'กำลังลบ...', didOpen: () => Swal.showLoading(), customClass: { popup: 'rounded-3xl' } });
            try {
                const res = await fetch(`/api/businesses/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                if (!res.ok) throw new Error('Delete failed');

                await Swal.fire({ icon: 'success', title: 'ลบสำเร็จ', confirmButtonColor: '#2563eb', customClass: { popup: 'rounded-3xl' } });
                fetchData();
            } catch (err) {
                console.error(err);
                Swal.fire('ผิดพลาด', 'ไม่สามารถลบข้อมูลได้', 'error');
            }
        }
      });
    }

    function formatCur(n) { return new Intl.NumberFormat('th-TH', { style: 'currency', currency: 'THB' }).format(n || 0); }
    function openModal(id) { document.getElementById(id).classList.remove('hidden'); if (id === 'modal-business') document.getElementById('partner-container').innerHTML = ''; }
    function closeModal(id) {
      document.getElementById(id).classList.add('hidden');
      if (id === 'modal-transaction') {
        document.getElementById('form-transaction').reset();
        document.getElementById('partner-info-display').classList.remove('active');
        document.getElementById('partner-selector-container').classList.add('hidden');
      }
    }

    function addPartnerRow() {
      const container = document.getElementById('partner-container');
      const rowId = 'partner-' + Date.now();
      const div = document.createElement('div');
      div.id = rowId;
      div.className = "grid grid-cols-1 md:grid-cols-3 gap-3 bg-slate-50 p-5 rounded-2xl relative border border-slate-100 shadow-sm transition-all";
      div.innerHTML = `
                <div class="col-span-1"><input type="text" placeholder="ชื่อผู้ลงทุน" class="p-row-name w-full bg-white border-0 rounded-xl px-4 py-2 text-sm outline-blue-500" required></div>
                <div class="col-span-1"><input type="number" placeholder="เงินลงทุน" class="p-row-amount w-full bg-white border-0 rounded-xl px-4 py-2 text-sm outline-blue-500" required></div>
                <div class="col-span-1"><input type="text" placeholder="ผลตอบแทน (%)" class="p-row-div w-full bg-white border-0 rounded-xl px-4 py-2 text-sm outline-blue-500"></div>
                <button type="button" onclick="document.getElementById('${rowId}').remove()" class="absolute -top-3 -right-3 bg-white text-red-500 rounded-full shadow-lg p-2 border hover:bg-red-50 transition-colors"><i data-lucide="x" class="w-3 h-3"></i></button>
            `;
      container.appendChild(div);
      lucide.createIcons();
    }

    document.getElementById('form-business').onsubmit = async function (e) {
      e.preventDefault();
      const formData = new FormData(this);
      const data = Object.fromEntries(formData);
      
      // Collect partners data
      data.partners = Array.from(document.querySelectorAll('#partner-container > div')).map(row => ({
        name: row.querySelector('.p-row-name').value, 
        amount: row.querySelector('.p-row-amount').value, 
        div: row.querySelector('.p-row-div').value
      }));

      Swal.fire({ title: 'กำลังบันทึกข้อมูล...', allowOutsideClick: false, didOpen: () => Swal.showLoading(), customClass: { popup: 'rounded-3xl' } });

      try {
        const response = await fetch('/api/businesses', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify(data)
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Failed to create business');
        }

        Swal.fire({ icon: 'success', title: 'สำเร็จ', text: 'บันทึกพอร์ตการลงทุนแล้ว', confirmButtonColor: '#2563eb', customClass: { popup: 'rounded-3xl' } });
        closeModal('modal-business');
        this.reset();
        document.getElementById('partner-container').innerHTML = '';
        fetchData(); // Refresh dashboard
      } catch (error) {
        console.error('Submission Error:', error);
        Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: error.message, confirmButtonColor: '#ef4444' });
      }
    };

    async function fetchTransactions() {
        try {
            const res = await fetch('/api/transactions');
            if (!res.ok) throw new Error('Failed to fetch transactions');
            const transactions = await res.json();
            
            const tbody = document.getElementById('transaction-table-body');
            tbody.innerHTML = '';
            
            if (transactions.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-8 text-slate-400 font-light">ยังไม่มีรายการธุรกรรม</td></tr>';
                return;
            }

            transactions.forEach(t => {
                const isIncome = t.type === 'ปันผล';
                const colorClass = isIncome ? 'text-green-600' : 'text-red-500';
                const sign = isIncome ? '+' : '-';
                
                const tr = document.createElement('tr');
                tr.className = "hover:bg-slate-50 transition-colors group/row";
                // Safe ID for functions
                const safeId = String(t.id).replace(/['"\\]/g, '');

                tr.innerHTML = `
                    <td class="px-6 py-4 text-slate-600 text-sm whitespace-nowrap">${new Date(t.date).toLocaleDateString('th-TH')}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-lg text-xs font-bold whitespace-nowrap ${isIncome ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}">
                            ${t.type}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-slate-700 font-medium text-sm">${t.business ? t.business.name : '-'}</td>
                    <td class="px-6 py-4 text-slate-500 text-sm">${t.partner ? t.partner.name : '-'}</td>
                    <td class="px-6 py-4 text-center font-bold whitespace-nowrap ${colorClass}">${sign}${formatCur(t.amount)}</td>
                    <td class="px-6 py-4 text-right text-slate-400 text-xs italic max-w-xs truncate" title="${t.note || ''}">${t.note || '-'}</td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex justify-center gap-2 opacity-100 md:opacity-0 group-hover/row:opacity-100 transition-all">
                             <button onclick="editTransaction('${safeId}')" class="p-2 bg-slate-50 text-blue-400 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors" title="แก้ไข">
                                <i data-lucide="edit-2" class="w-4 h-4"></i>
                            </button>
                            <button onclick="deleteTransaction('${safeId}')" class="p-2 bg-slate-50 text-slate-300 hover:bg-red-50 hover:text-red-600 rounded-lg transition-colors" title="ลบ">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
             lucide.createIcons();
        } catch (err) {
            console.error(err);
        }
    }

    async function deleteTransaction(id) {
        Swal.fire({
            title: 'ลบรายการ?',
            text: "ข้อมูลจะหายไปถาวร",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'ลบ',
            customClass: { popup: 'rounded-3xl' }
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const res = await fetch(`/api/transactions/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    
                    if (!res.ok) throw new Error('Delete failed');
                    
                    fetchData(); // Update stats
                    fetchTransactions(); // Update table
                    Swal.fire({ icon: 'success', title: 'ลบแล้ว', showConfirmButton: false, timer: 1500, customClass: { popup: 'rounded-3xl' } });
                } catch (err) {
                    Swal.fire('ผิดพลาด', 'ลบไม่ได้', 'error');
                }
            }
        });
    }

    async function editTransaction(id) {
        Swal.fire({ title: 'กำลังโหลด...', didOpen: () => Swal.showLoading(), allowOutsideClick: false, background: 'transparent', backdrop: 'rgba(0,0,0,0.5)' });
        try {
            const res = await fetch(`/api/transactions/${id}`);
            if (!res.ok) throw new Error('Fetch details failed');
            const data = await res.json(); // transaction data

            // Reset Form & Set Mode
            const form = document.getElementById('form-transaction');
            form.reset();
            
            // Set Hidden ID for Update Mode
            let idInput = document.getElementById('tx-update-id');
            if (!idInput) {
                idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.id = 'tx-update-id';
                idInput.name = 'id';
                form.prepend(idInput);
            }
            idInput.value = data.id;

            // Populate Fields
            document.getElementById('biz-dropdown').value = data.business_id;
            document.getElementById('tx-type-dropdown').value = data.type;
            
            // Populate Amount/Date/Note
            form.querySelector('input[name="amount"]').value = data.amount;
            form.querySelector('input[name="date"]').value = data.date;
            form.querySelector('textarea[name="note"]').value = data.note || '';

            // Handle Dynamic Partner Logic
            await handleTransactionModalChange(); // Will fetch partners based on biz-dropdown

            // If partner exists, select it (needs delay for dropdown population if async, but await above handles fetch)
            const partnerDropdown = document.getElementById('partner-dropdown');
            if (data.partner_id && partnerDropdown) {
                 // Wait briefly for dropdown render if necessary, but handleTransactionChange is awaited.
                 partnerDropdown.value = data.partner_id;
            }

            // Change Submit Button Text (Optional UX)
            const btn = form.querySelector('button[type="submit"]');
            btn.innerHTML = 'บันทึกการแก้ไข';
            btn.classList.add('bg-orange-500', 'hover:bg-orange-600');
            btn.classList.remove('bg-blue-600');

            openModal('modal-transaction');
            Swal.close();

        } catch (err) { 
            console.error(err);
            Swal.fire('Error', 'โหลดข้อมูลไม่สำเร็จ', 'error');
        }
    }

    // Hook into closeModal to reset edit mode state
    const originalClose = closeModal;
    closeModal = function(id) {
        originalClose(id);
        if (id === 'modal-transaction') {
            const form = document.getElementById('form-transaction');
            const idInput = document.getElementById('tx-update-id');
            if (idInput) idInput.remove(); // Remove ID -> Back to Create Mode
            
            const btn = form.querySelector('button[type="submit"]');
            btn.innerHTML = 'บันทึกธุรกรรม';
            btn.classList.remove('bg-orange-500', 'hover:bg-orange-600');
            btn.classList.add('bg-blue-600');
        }
    }

    document.getElementById('form-transaction').onsubmit = async function (e) {
      e.preventDefault();
      const formData = new FormData(this);
      const data = Object.fromEntries(formData);
      
      // Ensure partnerId is null if empty string
      if (!data.partnerId) {
          data.partnerId = null;
      }

      if (data.type === 'ปันผลหุ้นส่วน' && !data.partnerId) {
          Swal.fire('ข้อผิดพลาด', 'กรุณาเลือกหุ้นส่วนที่จะจ่ายปันผล', 'warning');
          return;
      }

      Swal.fire({ title: 'กำลังบันทึก...', allowOutsideClick: false, didOpen: () => Swal.showLoading(), customClass: { popup: 'rounded-3xl' } });

      try {
        const idInput = document.getElementById('tx-update-id');
        const isUpdate = idInput && idInput.value;
        
        const url = isUpdate ? `/api/transactions/${idInput.value}` : '/api/transactions';
        const method = isUpdate ? 'PUT' : 'POST';

        const response = await fetch(url, {
          method: method,
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify(data)
        });

        if (!response.ok) {
            const err = await response.json();
            throw new Error(err.message || 'Failed to save transaction');
        }

        Swal.fire({ icon: 'success', title: 'สำเร็จ', text: isUpdate ? 'แก้ไขข้อมูลเรียบร้อย' : 'บันทึกธุรกรรมเรียบร้อย', confirmButtonColor: '#2563eb', customClass: { popup: 'rounded-3xl' } });
        closeModal('modal-transaction'); // This triggers reset via hooked function
        
        // Manual reset anyway for safety
        if (!isUpdate) this.reset(); 

        fetchData(); // Refresh dashboard
        fetchTransactions(); // Refresh table
      } catch (error) {
        console.error(error);
        Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: error.message || 'ไม่สามารถบันทึกธุรกรรมได้', confirmButtonColor: '#ef4444' });
      }
    };
  </script>
</body>

</html>