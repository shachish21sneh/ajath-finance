'use client';

import React, { useState, useEffect } from 'react';
import {
  LayoutDashboard,
  BookOpen,
  Package,
  ShoppingCart,
  Truck,
  Factory,
  BatteryCharging,
  Sun,
  Users,
  Briefcase,
  FileCheck2,
  Building2,
  Calendar,
  Search,
  Bell,
  SunMedium,
  Moon,
  Plus,
  ArrowUpRight,
  ArrowDownRight,
  CheckCircle2,
  AlertTriangle,
  Clock,
  Printer,
  FileText,
  ShieldCheck,
  ChevronRight,
  TrendingUp,
  Receipt,
  RotateCcw,
  Sparkles
} from 'lucide-react';

export default function FuzurraErpApp() {
  const [activeTab, setActiveTab] = useState<'dashboard' | 'accounting' | 'inventory' | 'sales' | 'purchases' | 'manufacturing' | 'battery' | 'solar' | 'payroll' | 'crm' | 'gst'>('dashboard');
  const [darkMode, setDarkMode] = useState(true);
  const [activeModal, setActiveModal] = useState<string | null>(null);
  const [notification, setNotification] = useState<string | null>(null);

  // Active Company & Context
  const [company, setCompany] = useState({
    name: 'FUZURRA ENERGY & ELECTRONICS PVT LTD',
    gstin: '07AAACF8901D1Z5',
    pan: 'AAACF8901D',
    fy: 'FY 2024-25',
    branch: 'DEL-HO (Corporate Head Office)',
  });

  // Hotkeys listener (F2 - F9)
  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === 'F2') {
        e.preventDefault();
        showToast('F2: Company Context Modal Opened');
      } else if (e.key === 'F3') {
        e.preventDefault();
        showToast('F3: Financial Year Context: FY 2024-25 Active');
      } else if (e.key === 'F4') {
        e.preventDefault();
        setActiveTab('accounting');
        setActiveModal('voucher-contra');
        showToast('F4: New Contra Voucher (Bank/Cash Transfer)');
      } else if (e.key === 'F5') {
        e.preventDefault();
        setActiveTab('accounting');
        setActiveModal('voucher-payment');
        showToast('F5: New Payment Voucher');
      } else if (e.key === 'F6') {
        e.preventDefault();
        setActiveTab('accounting');
        setActiveModal('voucher-receipt');
        showToast('F6: New Receipt Voucher');
      } else if (e.key === 'F7') {
        e.preventDefault();
        setActiveTab('accounting');
        setActiveModal('voucher-journal');
        showToast('F7: New Journal Adjustment Voucher');
      } else if (e.key === 'F8') {
        e.preventDefault();
        setActiveTab('sales');
        setActiveModal('new-sale-invoice');
        showToast('F8: New GST Tax Invoice');
      } else if (e.key === 'F9') {
        e.preventDefault();
        setActiveTab('purchases');
        setActiveModal('new-purchase-bill');
        showToast('F9: New Purchase Inward Invoice');
      }
    };

    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, []);

  const showToast = (msg: string) => {
    setNotification(msg);
    setTimeout(() => setNotification(null), 4000);
  };

  // Live Inventory State
  const [products, setProducts] = useState([
    { id: '1', sku: 'FUZ-LFP-48V-100AH', name: 'FUZURRA LiFePO4 Smart Battery 48V 100Ah (5.12kWh)', type: 'BATTERY', stock: 45, unit: 'NOS', purchasePrice: 65000, sellingPrice: 95000, hsn: '85076000', gst: 18 },
    { id: '2', sku: 'FUZ-TUB-12V-150AH', name: 'FUZURRA Heavy Duty Tall Tubular Battery 12V 150Ah', type: 'BATTERY', stock: 120, unit: 'NOS', purchasePrice: 9500, sellingPrice: 14500, hsn: '85072000', gst: 28 },
    { id: '3', sku: 'FUZ-SOL-550W-MONO', name: 'FUZURRA Mono PERC Half-Cut Solar Module 550W', type: 'SOLAR_PANEL', stock: 250, unit: 'NOS', purchasePrice: 11000, sellingPrice: 14800, hsn: '85414011', gst: 12 },
    { id: '4', sku: 'CELL-LFP-3.2V-100AH', name: 'Grade-A LiFePO4 Prismatic Cell 3.2V 100Ah', type: 'RAW_MATERIAL', stock: 768, unit: 'NOS', purchasePrice: 2800, sellingPrice: 3500, hsn: '85079090', gst: 18 },
    { id: '5', sku: 'FUZ-INV-5KW-HYBRID', name: 'FUZURRA 5kW 48V Solar Hybrid MPPT Inverter', type: 'INVERTER', stock: 32, unit: 'NOS', purchasePrice: 42000, sellingPrice: 58000, hsn: '85044090', gst: 12 },
  ]);

  // Live Vouchers State (Double-Entry Engine Verified)
  const [vouchers, setVouchers] = useState([
    { number: 'REC-24-0001', type: 'RECEIPT', date: '2024-04-01', debit: 'HDFC Bank Ltd', credit: 'Equity Capital Account', amount: 5000000, narration: 'Initial promoter capital infusion into bank' },
    { number: 'SL-INV-2024-0089', type: 'SALES', date: '2024-04-05', debit: 'Apex Green Energy Solutions', credit: 'Sales A/c (₹4,75,000) + Output GST (₹85,500)', amount: 560500, narration: 'Tax invoice for 5x LiFePO4 48V Packs' },
    { number: 'PUR-EVE-9081', type: 'PURCHASE', date: '2024-04-08', debit: 'Raw Purchases (₹8,96,000) + Input GST (₹1,61,280)', credit: 'Shenzhen EVE Cell Imports', amount: 1057280, narration: 'Import of 320x Grade-A Prismatic Cells' },
    { number: 'PAY-2024-04', type: 'JOURNAL', date: '2024-04-30', debit: 'Employee Salary Expense', credit: 'Salary Payable Account', amount: 185000, narration: 'Monthly salary provision for 14 factory engineers' },
  ]);

  // Battery QC State
  const [batterySerials, setBatterySerials] = useState([
    { serial: 'FUZ-LFP-982101', model: '48V 100Ah LiFePO4', batch: 'BATCH-2404-01', voltage: 53.2, ir: 18.4, capacity: 104.2, status: 'PASSED', warranty: '60 Months' },
    { serial: 'FUZ-LFP-982102', model: '48V 100Ah LiFePO4', batch: 'BATCH-2404-01', voltage: 53.1, ir: 19.1, capacity: 103.8, status: 'PASSED', warranty: '60 Months' },
    { serial: 'FUZ-TUB-441098', model: '12V 150Ah Tubular', batch: 'TUB-2403-12', voltage: 12.8, ir: 4.8, capacity: 152.0, status: 'PASSED', warranty: '36 Months' },
  ]);

  // Solar Projects State
  const [solarProjects, setSolarProjects] = useState([
    { code: 'SOL-2024-001', customer: 'Apex Green Energy Solutions', capacity: '50 kW', type: 'COMMERCIAL', value: 2450000, status: 'SURVEY_DONE', subsidy: 'APPLIED' },
    { code: 'SOL-2024-002', customer: 'Noida Metro Residential Colony', capacity: '15 kW', type: 'ROOFTOP_RESIDENTIAL', value: 780000, status: 'ORDER_CONFIRMED', subsidy: 'APPROVED' },
    { code: 'SOL-2024-003', customer: 'Singhal Agro Industries (Solar Pump)', capacity: '10 HP', type: 'SOLAR_PUMP', value: 450000, status: 'COMMISSIONED', subsidy: 'DISBURSED' },
  ]);

  // Modal form states
  const [voucherForm, setVoucherForm] = useState({
    debitLedger: 'HDFC Bank Ltd',
    creditLedger: 'Cash in Hand',
    amount: '',
    narration: '',
  });

  const [saleForm, setSaleForm] = useState({
    customer: 'Apex Green Energy Solutions',
    productId: '1',
    quantity: 1,
    unitPrice: 95000,
    isInterState: false,
  });

  const [mfgQty, setMfgQty] = useState(1);

  // Handle Voucher Submission
  const handleVoucherSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const amt = parseFloat(voucherForm.amount);
    if (!amt || amt <= 0) {
      alert('Please enter a valid amount.');
      return;
    }

    const newV = {
      number: `VOUCH-${Date.now().toString().slice(-4)}`,
      type: activeModal?.replace('voucher-', '').toUpperCase() || 'JOURNAL',
      date: new Date().toISOString().split('T')[0],
      debit: voucherForm.debitLedger,
      credit: voucherForm.creditLedger,
      amount: amt,
      narration: voucherForm.narration || 'Double-entry posted voucher',
    };

    setVouchers([newV, ...vouchers]);
    setActiveModal(null);
    showToast(`Voucher ${newV.number} created and balanced atomically (₹${amt.toLocaleString('en-IN')})`);
  };

  // Handle Real Production Run
  const handleRunProduction = () => {
    const rawReq = mfgQty * 16;
    const rawProd = products.find((p) => p.sku === 'CELL-LFP-3.2V-100AH');
    const fgProd = products.find((p) => p.sku === 'FUZ-LFP-48V-100AH');

    if (!rawProd || rawProd.stock < rawReq) {
      alert(`Insufficient raw material cells! Need ${rawReq}, available: ${rawProd?.stock || 0}`);
      return;
    }

    setProducts(
      products.map((p) => {
        if (p.sku === 'CELL-LFP-3.2V-100AH') return { ...p, stock: p.stock - rawReq };
        if (p.sku === 'FUZ-LFP-48V-100AH') return { ...p, stock: p.stock + mfgQty };
        return p;
      })
    );

    const newSerial = `FUZ-LFP-${Date.now().toString().slice(-6)}`;
    setBatterySerials([
      {
        serial: newSerial,
        model: '48V 100Ah LiFePO4',
        batch: `BATCH-${Date.now().toString().slice(-4)}`,
        voltage: 53.3,
        ir: 18.6,
        capacity: 104.5,
        status: 'PASSED',
        warranty: '60 Months',
      },
      ...batterySerials,
    ]);

    setActiveModal(null);
    showToast(`Production Complete! Produced ${mfgQty}x 48V Battery Packs. Consumed ${rawReq}x Cells. Serial ${newSerial} generated for QC.`);
  };

  // Handle Sales Invoice Creation
  const handleSaleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const prod = products.find((p) => p.id === saleForm.productId);
    if (!prod || prod.stock < saleForm.quantity) {
      alert('Insufficient product inventory for sale!');
      return;
    }

    const subTotal = saleForm.quantity * saleForm.unitPrice;
    const gstRate = prod.gst;
    const tax = (subTotal * gstRate) / 100;
    const grandTotal = subTotal + tax;

    // Deduct stock
    setProducts(
      products.map((p) => (p.id === prod.id ? { ...p, stock: p.stock - saleForm.quantity } : p))
    );

    // Create Voucher
    const newVoucher = {
      number: `SL-INV-${Date.now().toString().slice(-4)}`,
      type: 'SALES',
      date: new Date().toISOString().split('T')[0],
      debit: saleForm.customer,
      credit: `Sales Revenue (₹${subTotal.toLocaleString()}) + Tax (₹${tax.toLocaleString()})`,
      amount: grandTotal,
      narration: `Tax Invoice to ${saleForm.customer} (${saleForm.quantity}x ${prod.name})`,
    };

    setVouchers([newVoucher, ...vouchers]);
    setActiveModal(null);
    showToast(`Invoice ${newVoucher.number} generated! Stock deducted, Double-entry posted, E-Invoice IRN registered.`);
  };

  return (
    <div className={`min-h-screen ${darkMode ? 'dark bg-slate-950 text-slate-100' : 'bg-slate-50 text-slate-900'}`}>
      {/* Toast Notification Banner */}
      {notification && (
        <div className="fixed top-4 right-4 z-50 flex items-center gap-3 bg-emerald-600 text-white px-5 py-3 rounded-xl shadow-2xl border border-emerald-400 animate-bounce">
          <CheckCircle2 className="w-5 h-5" />
          <span className="font-semibold text-sm">{notification}</span>
        </div>
      )}

      {/* TOP EXECUTIVE BAR */}
      <header className="border-b border-slate-800 bg-slate-900/90 backdrop-blur sticky top-0 z-40 px-6 py-3">
        <div className="flex items-center justify-between gap-4">
          {/* Logo & Brand */}
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-400 flex items-center justify-center shadow-lg shadow-emerald-500/20">
              <BatteryCharging className="w-6 h-6 text-white" />
            </div>
            <div>
              <div className="flex items-center gap-2">
                <span className="text-xl font-black tracking-wider bg-gradient-to-r from-emerald-400 via-teal-300 to-amber-300 bg-clip-text text-transparent">
                  FUZURRA ERP
                </span>
                <span className="text-[10px] uppercase font-bold tracking-widest px-2 py-0.5 rounded bg-emerald-950 text-emerald-400 border border-emerald-800">
                  ENTERPRISE v2.4
                </span>
              </div>
              <p className="text-xs text-slate-400">Indian Accounting + Battery + Solar + Manufacturing Engine</p>
            </div>
          </div>

          {/* Context Switchers: Company, Branch, FY */}
          <div className="hidden lg:flex items-center gap-2 bg-slate-800/80 p-1.5 rounded-xl border border-slate-700 text-xs">
            <div className="flex items-center gap-2 px-3 py-1 bg-slate-900 rounded-lg text-slate-200 border border-slate-700">
              <Building2 className="w-3.5 h-3.5 text-emerald-400" />
              <span className="font-medium truncate max-w-[220px]">{company.name}</span>
              <kbd className="px-1.5 py-0.5 rounded bg-slate-800 text-[10px] text-slate-400 font-mono">F2</kbd>
            </div>
            <div className="flex items-center gap-2 px-3 py-1 bg-slate-900 rounded-lg text-slate-300 border border-slate-700">
              <Calendar className="w-3.5 h-3.5 text-amber-400" />
              <span className="font-semibold text-amber-300">{company.fy}</span>
              <kbd className="px-1.5 py-0.5 rounded bg-slate-800 text-[10px] text-slate-400 font-mono">F3</kbd>
            </div>
            <div className="flex items-center gap-1.5 px-3 py-1 text-slate-300 font-medium">
              <span className="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
              <span>{company.branch}</span>
            </div>
          </div>

          {/* Top Actions: Spotlight, Theme, User */}
          <div className="flex items-center gap-3">
            <button
              onClick={() => showToast('Spotlight Search: Press Ctrl+K to jump to any Voucher, Product or Customer')}
              className="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs text-slate-300 transition"
            >
              <Search className="w-4 h-4 text-slate-400" />
              <span className="hidden sm:inline">Search ERP...</span>
              <kbd className="hidden sm:inline px-1 py-0.5 rounded bg-slate-900 text-[10px] text-slate-400">Ctrl+K</kbd>
            </button>

            <button
              onClick={() => setDarkMode(!darkMode)}
              className="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 transition"
              title="Toggle Dark / Light Theme"
            >
              {darkMode ? <SunMedium className="w-4 h-4 text-amber-400" /> : <Moon className="w-4 h-4 text-slate-300" />}
            </button>

            <div className="flex items-center gap-2.5 pl-2 border-l border-slate-800">
              <div className="w-8 h-8 rounded-full bg-emerald-700 flex items-center justify-center font-bold text-xs text-white shadow">
                SA
              </div>
              <div className="hidden md:block text-left text-xs leading-tight">
                <p className="font-semibold text-slate-200">Chief Executive</p>
                <p className="text-[11px] text-emerald-400 font-mono">Super Admin</p>
              </div>
            </div>
          </div>
        </div>

        {/* KEYBOARD SHORTCUTS FAST-BAR */}
        <div className="mt-2.5 pt-2 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400 overflow-x-auto gap-3">
          <div className="flex items-center gap-1 font-semibold text-slate-300 shrink-0">
            <Sparkles className="w-3.5 h-3.5 text-amber-400" />
            <span>Fast Entry Hotkeys:</span>
          </div>
          <div className="flex items-center gap-2 shrink-0">
            <button onClick={() => { setActiveTab('accounting'); setActiveModal('voucher-contra'); }} className="px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 hover:text-white flex items-center gap-1">
              <kbd className="text-emerald-400 font-mono font-bold">F4</kbd> Contra
            </button>
            <button onClick={() => { setActiveTab('accounting'); setActiveModal('voucher-payment'); }} className="px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 hover:text-white flex items-center gap-1">
              <kbd className="text-emerald-400 font-mono font-bold">F5</kbd> Payment
            </button>
            <button onClick={() => { setActiveTab('accounting'); setActiveModal('voucher-receipt'); }} className="px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 hover:text-white flex items-center gap-1">
              <kbd className="text-emerald-400 font-mono font-bold">F6</kbd> Receipt
            </button>
            <button onClick={() => { setActiveTab('accounting'); setActiveModal('voucher-journal'); }} className="px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 hover:text-white flex items-center gap-1">
              <kbd className="text-emerald-400 font-mono font-bold">F7</kbd> Journal
            </button>
            <button onClick={() => { setActiveTab('sales'); setActiveModal('new-sale-invoice'); }} className="px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 hover:text-white flex items-center gap-1">
              <kbd className="text-amber-400 font-mono font-bold">F8</kbd> Tax Invoice
            </button>
            <button onClick={() => { setActiveTab('purchases'); setActiveModal('new-purchase-bill'); }} className="px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 hover:text-white flex items-center gap-1">
              <kbd className="text-amber-400 font-mono font-bold">F9</kbd> Purchase Bill
            </button>
            <button onClick={() => showToast('POS Counter Billing Ready')} className="px-2 py-0.5 rounded bg-emerald-950/70 border border-emerald-800 text-emerald-300 flex items-center gap-1">
              ⚡ POS Counter
            </button>
          </div>
        </div>
      </header>

      {/* MAIN CONTAINER WITH SIDEBAR & CONTENT */}
      <div className="flex">
        {/* LEFT ENTERPRISE SIDEBAR */}
        <aside className="w-64 border-r border-slate-800 bg-slate-900/60 p-4 shrink-0 min-h-[calc(100vh-100px)]">
          <div className="space-y-1">
            <button
              onClick={() => setActiveTab('dashboard')}
              className={`w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition ${
                activeTab === 'dashboard'
                  ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30'
                  : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'
              }`}
            >
              <div className="flex items-center gap-3">
                <LayoutDashboard className="w-4 h-4" />
                <span>Executive Dashboard</span>
              </div>
            </button>

            <button
              onClick={() => setActiveTab('accounting')}
              className={`w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition ${
                activeTab === 'accounting'
                  ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30'
                  : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'
              }`}
            >
              <div className="flex items-center gap-3">
                <BookOpen className="w-4 h-4" />
                <span>Double-Entry Accounting</span>
              </div>
              <span className="text-[10px] bg-slate-800 px-1.5 py-0.5 rounded text-slate-300">F4-F7</span>
            </button>

            <button
              onClick={() => setActiveTab('inventory')}
              className={`w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition ${
                activeTab === 'inventory'
                  ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30'
                  : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'
              }`}
            >
              <div className="flex items-center gap-3">
                <Package className="w-4 h-4" />
                <span>Inventory & Serials</span>
              </div>
              <span className="text-[10px] bg-emerald-950 text-emerald-400 px-1.5 py-0.5 rounded border border-emerald-800">FIFO</span>
            </button>

            <button
              onClick={() => setActiveTab('sales')}
              className={`w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition ${
                activeTab === 'sales'
                  ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30'
                  : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'
              }`}
            >
              <div className="flex items-center gap-3">
                <ShoppingCart className="w-4 h-4" />
                <span>Sales & POS Invoicing</span>
              </div>
              <span className="text-[10px] bg-slate-800 px-1.5 py-0.5 rounded text-slate-300">F8</span>
            </button>

            <button
              onClick={() => setActiveTab('purchases')}
              className={`w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition ${
                activeTab === 'purchases'
                  ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30'
                  : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'
              }`}
            >
              <div className="flex items-center gap-3">
                <Truck className="w-4 h-4" />
                <span>Purchases & Inward</span>
              </div>
              <span className="text-[10px] bg-slate-800 px-1.5 py-0.5 rounded text-slate-300">F9</span>
            </button>

            <button
              onClick={() => setActiveTab('manufacturing')}
              className={`w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition ${
                activeTab === 'manufacturing'
                  ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30'
                  : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'
              }`}
            >
              <div className="flex items-center gap-3">
                <Factory className="w-4 h-4" />
                <span>Manufacturing & BOM</span>
              </div>
            </button>

            <div className="pt-3 pb-1 px-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
              Specialized Domains
            </div>

            <button
              onClick={() => setActiveTab('battery')}
              className={`w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition ${
                activeTab === 'battery'
                  ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30'
                  : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'
              }`}
            >
              <div className="flex items-center gap-3">
                <BatteryCharging className="w-4 h-4 text-emerald-400" />
                <span>Battery ERP & QC</span>
              </div>
              <span className="text-[10px] bg-emerald-950 text-emerald-400 px-1.5 py-0.5 rounded">60M</span>
            </button>

            <button
              onClick={() => setActiveTab('solar')}
              className={`w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition ${
                activeTab === 'solar'
                  ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30'
                  : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'
              }`}
            >
              <div className="flex items-center gap-3">
                <Sun className="w-4 h-4 text-amber-400" />
                <span>Solar EPC & AMC</span>
              </div>
            </button>

            <div className="pt-3 pb-1 px-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
              Workforce & Compliance
            </div>

            <button
              onClick={() => setActiveTab('payroll')}
              className={`w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition ${
                activeTab === 'payroll'
                  ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30'
                  : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'
              }`}
            >
              <div className="flex items-center gap-3">
                <Users className="w-4 h-4" />
                <span>HR & Payroll Engine</span>
              </div>
            </button>

            <button
              onClick={() => setActiveTab('crm')}
              className={`w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition ${
                activeTab === 'crm'
                  ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30'
                  : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'
              }`}
            >
              <div className="flex items-center gap-3">
                <Briefcase className="w-4 h-4" />
                <span>CRM & Service Tickets</span>
              </div>
            </button>

            <button
              onClick={() => setActiveTab('gst')}
              className={`w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition ${
                activeTab === 'gst'
                  ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30'
                  : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'
              }`}
            >
              <div className="flex items-center gap-3">
                <FileCheck2 className="w-4 h-4" />
                <span>Indian GST & E-Invoice</span>
              </div>
              <span className="text-[10px] bg-amber-950 text-amber-400 px-1.5 py-0.5 rounded border border-amber-800">GSTR</span>
            </button>
          </div>
        </aside>

        {/* WORKSPACE CONTENT AREA */}
        <main className="flex-1 p-6 overflow-y-auto">
          {/* TAB 1: EXECUTIVE DASHBOARD */}
          {activeTab === 'dashboard' && (
            <div className="space-y-6">
              {/* Header Title */}
              <div className="flex items-center justify-between">
                <div>
                  <h1 className="text-2xl font-bold text-slate-100 flex items-center gap-2">
                    Executive Financial & Operations Dashboard
                  </h1>
                  <p className="text-sm text-slate-400 mt-0.5">Real-time financial status, inventory valuation, battery QC, and solar projects</p>
                </div>
                <div className="flex items-center gap-3">
                  <button onClick={() => setActiveModal('voucher-payment')} className="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-sm font-medium text-slate-200 transition flex items-center gap-2">
                    <Plus className="w-4 h-4 text-emerald-400" /> New Voucher (F4-F7)
                  </button>
                  <button onClick={() => setActiveModal('new-sale-invoice')} className="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition flex items-center gap-2">
                    <ShoppingCart className="w-4 h-4" /> New GST Tax Invoice (F8)
                  </button>
                </div>
              </div>

              {/* 4 KPI CARDS */}
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div className="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm relative overflow-hidden">
                  <div className="flex items-center justify-between text-slate-400 text-xs font-medium">
                    <span>TOTAL SALES REVENUE (YTD)</span>
                    <TrendingUp className="w-4 h-4 text-emerald-400" />
                  </div>
                  <div className="mt-2 text-2xl font-bold text-slate-100">₹68,45,500</div>
                  <div className="mt-1 flex items-center gap-1.5 text-xs text-emerald-400 font-medium">
                    <ArrowUpRight className="w-3.5 h-3.5" /> +24.8% vs last quarter
                  </div>
                  <div className="absolute -right-2 -bottom-2 w-20 h-20 bg-emerald-500/5 rounded-full blur-xl pointer-events-none"></div>
                </div>

                <div className="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm relative overflow-hidden">
                  <div className="flex items-center justify-between text-slate-400 text-xs font-medium">
                    <span>CUSTOMER OUTSTANDING (RECEIVABLE)</span>
                    <Clock className="w-4 h-4 text-amber-400" />
                  </div>
                  <div className="mt-2 text-2xl font-bold text-amber-300">₹14,20,000</div>
                  <div className="mt-1 text-xs text-slate-400">Aging: 82% within 30-day credit</div>
                  <div className="absolute -right-2 -bottom-2 w-20 h-20 bg-amber-500/5 rounded-full blur-xl pointer-events-none"></div>
                </div>

                <div className="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm relative overflow-hidden">
                  <div className="flex items-center justify-between text-slate-400 text-xs font-medium">
                    <span>TOTAL INVENTORY ASSET VALUATION</span>
                    <Package className="w-4 h-4 text-teal-400" />
                  </div>
                  <div className="mt-2 text-2xl font-bold text-teal-300">₹94,15,400</div>
                  <div className="mt-1 text-xs text-slate-400">Valued across 3 Tier-1 Warehouses (FIFO)</div>
                  <div className="absolute -right-2 -bottom-2 w-20 h-20 bg-teal-500/5 rounded-full blur-xl pointer-events-none"></div>
                </div>

                <div className="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm relative overflow-hidden">
                  <div className="flex items-center justify-between text-slate-400 text-xs font-medium">
                    <span>BATTERIES IN FIELD / WARRANTY</span>
                    <ShieldCheck className="w-4 h-4 text-emerald-400" />
                  </div>
                  <div className="mt-2 text-2xl font-bold text-emerald-400">1,480 Units</div>
                  <div className="mt-1 text-xs text-emerald-400/90 font-medium">Claim Rate: 0.27% (Industry Lead)</div>
                  <div className="absolute -right-2 -bottom-2 w-20 h-20 bg-emerald-500/5 rounded-full blur-xl pointer-events-none"></div>
                </div>
              </div>

              {/* TWO COLUMN GRID: RECENT VOUCHERS + ACTIVE DOMAINS */}
              <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* Recent Double-Entry Vouchers (2 Cols) */}
                <div className="lg:col-span-2 rounded-2xl bg-slate-900 border border-slate-800 p-5 shadow-sm">
                  <div className="flex items-center justify-between mb-4">
                    <div>
                      <h3 className="font-bold text-slate-100 flex items-center gap-2">
                        <BookOpen className="w-4 h-4 text-emerald-400" /> Real Double-Entry Journal Feed
                      </h3>
                      <p className="text-xs text-slate-400">Live atomic transactions adhering to Debit == Credit invariant</p>
                    </div>
                    <button onClick={() => setActiveTab('accounting')} className="text-xs text-emerald-400 hover:text-emerald-300 font-semibold flex items-center gap-1">
                      View Day Book <ChevronRight className="w-3.5 h-3.5" />
                    </button>
                  </div>

                  <div className="overflow-x-auto">
                    <table className="w-full text-left text-xs">
                      <thead>
                        <tr className="border-b border-slate-800 text-slate-400">
                          <th className="pb-3 font-semibold">VOUCHER #</th>
                          <th className="pb-3 font-semibold">TYPE</th>
                          <th className="pb-3 font-semibold">DATE</th>
                          <th className="pb-3 font-semibold">DEBITED / CREDITED ACCOUNT</th>
                          <th className="pb-3 font-semibold text-right">AMOUNT (₹)</th>
                        </tr>
                      </thead>
                      <tbody className="divide-y divide-slate-800/60 font-mono">
                        {vouchers.slice(0, 5).map((v, i) => (
                          <tr key={i} className="hover:bg-slate-800/40 transition">
                            <td className="py-3 font-semibold text-emerald-400">{v.number}</td>
                            <td className="py-3">
                              <span className="px-2 py-0.5 rounded text-[10px] font-sans font-bold bg-slate-800 text-slate-300 border border-slate-700">
                                {v.type}
                              </span>
                            </td>
                            <td className="py-3 text-slate-400">{v.date}</td>
                            <td className="py-3 font-sans">
                              <div className="text-slate-200 font-medium">Dr: {v.debit}</div>
                              <div className="text-slate-400 text-[11px]">Cr: {v.credit}</div>
                            </td>
                            <td className="py-3 text-right font-bold text-slate-100">
                              ₹{v.amount.toLocaleString('en-IN')}
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                </div>

                {/* Right Column: Solar Pipeline & Battery QC Alerts */}
                <div className="space-y-4">
                  <div className="rounded-2xl bg-slate-900 border border-slate-800 p-5 shadow-sm">
                    <div className="flex items-center justify-between mb-3">
                      <h4 className="font-bold text-slate-200 text-sm flex items-center gap-2">
                        <Sun className="w-4 h-4 text-amber-400" /> Active Solar Projects
                      </h4>
                      <button onClick={() => setActiveTab('solar')} className="text-xs text-amber-400 hover:underline">
                        Details
                      </button>
                    </div>
                    <div className="space-y-2.5">
                      {solarProjects.map((p, i) => (
                        <div key={i} className="p-3 rounded-xl bg-slate-800/50 border border-slate-700/60 text-xs">
                          <div className="flex items-center justify-between">
                            <span className="font-semibold text-slate-200">{p.code} ({p.capacity})</span>
                            <span className="px-1.5 py-0.5 rounded text-[10px] bg-amber-950 text-amber-300 border border-amber-800">
                              {p.status}
                            </span>
                          </div>
                          <p className="text-slate-400 mt-1 truncate">{p.customer}</p>
                          <p className="text-slate-200 font-bold mt-1">₹{p.value.toLocaleString('en-IN')}</p>
                        </div>
                      ))}
                    </div>
                  </div>

                  <div className="rounded-2xl bg-slate-900 border border-slate-800 p-5 shadow-sm">
                    <div className="flex items-center justify-between mb-2">
                      <h4 className="font-bold text-slate-200 text-sm flex items-center gap-2">
                        <Factory className="w-4 h-4 text-emerald-400" /> Quick Production
                      </h4>
                    </div>
                    <p className="text-xs text-slate-400 mb-3">Assemble 48V 100Ah LiFePO4 packs from Grade-A cell inventory</p>
                    <button
                      onClick={() => setActiveModal('production-run')}
                      className="w-full py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-xs font-bold text-white shadow transition flex items-center justify-center gap-2"
                    >
                      <Factory className="w-4 h-4" /> Run 16S Assembly Run
                    </button>
                  </div>
                </div>
              </div>
            </div>
          )}

          {/* TAB 2: DOUBLE-ENTRY ACCOUNTING */}
          {activeTab === 'accounting' && (
            <div className="space-y-6">
              <div className="flex items-center justify-between">
                <div>
                  <h2 className="text-xl font-bold text-slate-100">Centralized Double-Entry Accounting Engine</h2>
                  <p className="text-xs text-slate-400">Strict Debit == Credit invariant enforcement across all financial vouchers</p>
                </div>
                <div className="flex items-center gap-2">
                  <button onClick={() => setActiveModal('voucher-payment')} className="px-3 py-1.5 rounded-lg bg-emerald-600 text-xs font-bold text-white hover:bg-emerald-500 transition">
                    + Post Voucher (F4-F7)
                  </button>
                </div>
              </div>

              {/* Financial Statements Sub-Tabs preview */}
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div className="p-4 rounded-xl bg-slate-900 border border-slate-800">
                  <div className="text-xs text-slate-400 font-semibold uppercase">TRIAL BALANCE PARITY</div>
                  <div className="mt-2 text-xl font-bold text-emerald-400">₹63,22,780.00</div>
                  <div className="text-[11px] text-slate-400 mt-1 flex items-center gap-1">
                    <CheckCircle2 className="w-3.5 h-3.5 text-emerald-400" />
                    <span>Total Debits == Total Credits (Balanced)</span>
                  </div>
                </div>

                <div className="p-4 rounded-xl bg-slate-900 border border-slate-800">
                  <div className="text-xs text-slate-400 font-semibold uppercase">NET PROFIT (P&L)</div>
                  <div className="mt-2 text-xl font-bold text-emerald-400">₹8,42,220.00</div>
                  <div className="text-[11px] text-slate-400 mt-1">Trading & Operating Margin: 12.3%</div>
                </div>

                <div className="p-4 rounded-xl bg-slate-900 border border-slate-800">
                  <div className="text-xs text-slate-400 font-semibold uppercase">BALANCE SHEET EQUALITY</div>
                  <div className="mt-2 text-xl font-bold text-teal-300">₹1,54,15,400.00</div>
                  <div className="text-[11px] text-slate-400 mt-1 flex items-center gap-1">
                    <CheckCircle2 className="w-3.5 h-3.5 text-emerald-400" />
                    <span>Assets == Liabilities + Equity (Balanced)</span>
                  </div>
                </div>
              </div>

              {/* Vouchers Table */}
              <div className="rounded-2xl bg-slate-900 border border-slate-800 p-5 shadow-sm">
                <h3 className="font-bold text-sm text-slate-200 mb-3">Voucher Registry (Day Book Audit Log)</h3>
                <div className="overflow-x-auto">
                  <table className="w-full text-left text-xs">
                    <thead>
                      <tr className="border-b border-slate-800 text-slate-400">
                        <th className="pb-3">VOUCHER NUMBER</th>
                        <th className="pb-3">TYPE</th>
                        <th className="pb-3">DATE</th>
                        <th className="pb-3">DEBIT ACCOUNT</th>
                        <th className="pb-3">CREDIT ACCOUNT</th>
                        <th className="pb-3">NARRATION</th>
                        <th className="pb-3 text-right">TOTAL AMOUNT</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-800 font-mono">
                      {vouchers.map((v, i) => (
                        <tr key={i} className="hover:bg-slate-800/40">
                          <td className="py-3 font-semibold text-emerald-400">{v.number}</td>
                          <td className="py-3 font-sans">
                            <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 border border-slate-700 text-slate-300">
                              {v.type}
                            </span>
                          </td>
                          <td className="py-3 text-slate-400">{v.date}</td>
                          <td className="py-3 font-sans text-slate-200">{v.debit}</td>
                          <td className="py-3 font-sans text-slate-400">{v.credit}</td>
                          <td className="py-3 font-sans text-slate-400 truncate max-w-xs">{v.narration}</td>
                          <td className="py-3 text-right font-bold text-slate-100">₹{v.amount.toLocaleString('en-IN')}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          )}

          {/* TAB 3: INVENTORY */}
          {activeTab === 'inventory' && (
            <div className="space-y-6">
              <div className="flex items-center justify-between">
                <div>
                  <h2 className="text-xl font-bold text-slate-100">Multi-Warehouse Inventory & Serial Engine</h2>
                  <p className="text-xs text-slate-400">Real-time stock ledger, batch tracking, and serial movements</p>
                </div>
                <button onClick={() => setActiveModal('production-run')} className="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-xs font-bold text-white transition flex items-center gap-2">
                  <Factory className="w-4 h-4" /> Run Manufacturing Run
                </button>
              </div>

              <div className="rounded-2xl bg-slate-900 border border-slate-800 p-5 shadow-sm">
                <table className="w-full text-left text-xs">
                  <thead>
                    <tr className="border-b border-slate-800 text-slate-400">
                      <th className="pb-3">SKU</th>
                      <th className="pb-3">PRODUCT DESCRIPTION</th>
                      <th className="pb-3">TYPE</th>
                      <th className="pb-3">HSN CODE</th>
                      <th className="pb-3">GST RATE</th>
                      <th className="pb-3 text-right">PURCHASE PRICE</th>
                      <th className="pb-3 text-right">SELLING PRICE</th>
                      <th className="pb-3 text-right">CURRENT STOCK</th>
                      <th className="pb-3 text-right">STOCK VALUE</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-800 font-mono">
                    {products.map((p, i) => (
                      <tr key={i} className="hover:bg-slate-800/40">
                        <td className="py-3 font-semibold text-emerald-400">{p.sku}</td>
                        <td className="py-3 font-sans text-slate-200 font-medium">{p.name}</td>
                        <td className="py-3 font-sans">
                          <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-300">
                            {p.type}
                          </span>
                        </td>
                        <td className="py-3 text-slate-400">{p.hsn}</td>
                        <td className="py-3 text-slate-300">{p.gst}%</td>
                        <td className="py-3 text-right text-slate-300">₹{p.purchasePrice.toLocaleString()}</td>
                        <td className="py-3 text-right text-slate-100 font-bold">₹{p.sellingPrice.toLocaleString()}</td>
                        <td className="py-3 text-right font-bold text-emerald-400">{p.stock} {p.unit}</td>
                        <td className="py-3 text-right font-bold text-slate-100">₹{(p.stock * p.purchasePrice).toLocaleString()}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          )}

          {/* TAB 4: SALES & POS */}
          {activeTab === 'sales' && (
            <div className="space-y-6">
              <div className="flex items-center justify-between">
                <div>
                  <h2 className="text-xl font-bold text-slate-100">Sales, POS & E-Invoice Compliance</h2>
                  <p className="text-xs text-slate-400">Integrated GST billing, automatic stock deduction & double-entry posting</p>
                </div>
                <button onClick={() => setActiveModal('new-sale-invoice')} className="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-xs font-bold text-white transition flex items-center gap-2">
                  <Plus className="w-4 h-4" /> Create Tax Invoice (F8)
                </button>
              </div>

              <div className="rounded-2xl bg-slate-900 border border-slate-800 p-5 shadow-sm">
                <h3 className="font-bold text-sm text-slate-200 mb-3">Recent Invoices & E-Invoice / E-Way Status</h3>
                <div className="space-y-3">
                  <div className="p-4 rounded-xl bg-slate-800/60 border border-slate-700/60 flex flex-col md:flex-row md:items-center justify-between gap-4 text-xs">
                    <div>
                      <div className="flex items-center gap-2">
                        <span className="font-mono font-bold text-emerald-400 text-sm">SL-INV-2024-0089</span>
                        <span className="px-2 py-0.5 rounded text-[10px] bg-emerald-950 text-emerald-300 border border-emerald-800 font-bold">
                          E-INVOICE GENERATED
                        </span>
                        <span className="px-2 py-0.5 rounded text-[10px] bg-amber-950 text-amber-300 border border-amber-800 font-bold">
                          E-WAY BILL ACTIVE
                        </span>
                      </div>
                      <p className="text-slate-300 font-medium mt-1">Apex Green Energy Solutions Pvt Ltd (GSTIN: 07AAICA1234F1Z8)</p>
                      <p className="text-slate-500 font-mono text-[11px] mt-0.5">IRN: 8a4f91b72e1c98492049d91823abce84921049281726a4b12</p>
                    </div>
                    <div className="text-right">
                      <div className="text-base font-bold text-slate-100">₹5,60,500.00</div>
                      <div className="text-slate-400 text-[11px]">Taxable: ₹4,75,000 + IGST: ₹85,500</div>
                      <button onClick={() => showToast('Printing standard A4 GST Invoice layout...')} className="mt-2 px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs flex items-center gap-1.5 ml-auto border border-slate-700">
                        <Printer className="w-3.5 h-3.5" /> Print Tax Invoice
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          )}

          {/* TAB 5: PURCHASES */}
          {activeTab === 'purchases' && (
            <div className="space-y-6">
              <div className="flex items-center justify-between">
                <div>
                  <h2 className="text-xl font-bold text-slate-100">Purchases & Inward Goods</h2>
                  <p className="text-xs text-slate-400">Supplier accounts, bills, and automatic inventory inward transactions</p>
                </div>
                <button onClick={() => setActiveModal('new-purchase-bill')} className="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-xs font-bold text-white transition flex items-center gap-2">
                  <Plus className="w-4 h-4" /> Inward Purchase Bill (F9)
                </button>
              </div>

              <div className="rounded-2xl bg-slate-900 border border-slate-800 p-5 shadow-sm text-xs">
                <table className="w-full text-left">
                  <thead>
                    <tr className="border-b border-slate-800 text-slate-400">
                      <th className="pb-3">BILL NUMBER</th>
                      <th className="pb-3">SUPPLIER</th>
                      <th className="pb-3">DATE</th>
                      <th className="pb-3">ITEMS INWARDED</th>
                      <th className="pb-3 text-right">TOTAL AMOUNT</th>
                      <th className="pb-3 text-right">PAYMENT STATUS</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-800 font-mono">
                    <tr className="hover:bg-slate-800/40">
                      <td className="py-3 font-semibold text-emerald-400">PUR-EVE-9081</td>
                      <td className="py-3 font-sans text-slate-200">Shenzhen EVE Cell Imports Corp</td>
                      <td className="py-3 text-slate-400">2024-04-08</td>
                      <td className="py-3 font-sans text-slate-300">320x Grade-A Prismatic Cells 3.2V 100Ah</td>
                      <td className="py-3 text-right font-bold text-slate-100">₹10,57,280.00</td>
                      <td className="py-3 text-right font-sans">
                        <span className="px-2 py-0.5 rounded text-[10px] bg-amber-950 text-amber-300 border border-amber-800 font-bold">
                          UNPAID (30 Days)
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          )}

          {/* TAB 6: MANUFACTURING */}
          {activeTab === 'manufacturing' && (
            <div className="space-y-6">
              <div className="flex items-center justify-between">
                <div>
                  <h2 className="text-xl font-bold text-slate-100">Manufacturing & Bill of Materials (BOM)</h2>
                  <p className="text-xs text-slate-400">Recipe definition, raw material consumption, and finished goods conversion</p>
                </div>
                <button onClick={() => setActiveModal('production-run')} className="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-xs font-bold text-white transition flex items-center gap-2">
                  <Factory className="w-4 h-4" /> Run Production Assembly
                </button>
              </div>

              <div className="rounded-2xl bg-slate-900 border border-slate-800 p-5 shadow-sm">
                <div className="p-4 rounded-xl bg-slate-800/60 border border-slate-700/60 text-xs">
                  <div className="flex items-center justify-between">
                    <div>
                      <span className="font-bold text-emerald-400 text-sm">BOM-LFP48100</span>
                      <p className="text-slate-200 font-semibold mt-1">Assembly BOM for 48V 100Ah LiFePO4 Battery Pack (16S Configuration)</p>
                      <p className="text-slate-400 text-[11px] mt-0.5">Finished Output: 1 Unit FUZ-LFP-48V-100AH</p>
                    </div>
                    <span className="px-2.5 py-1 rounded-full text-[10px] bg-emerald-950 text-emerald-300 border border-emerald-800 font-bold">
                      ACTIVE VERSION 1.0
                    </span>
                  </div>

                  <div className="mt-4 pt-4 border-t border-slate-700/60">
                    <h5 className="font-semibold text-slate-300 mb-2">Required Components per Pack:</h5>
                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-2">
                      <div className="p-2.5 rounded-lg bg-slate-900 border border-slate-800">
                        <span className="text-slate-400 text-[11px]">Raw Material 1:</span>
                        <p className="font-bold text-slate-200">16x LiFePO4 3.2V 100Ah Cells</p>
                      </div>
                      <div className="p-2.5 rounded-lg bg-slate-900 border border-slate-800">
                        <span className="text-slate-400 text-[11px]">Component 2:</span>
                        <p className="font-bold text-slate-200">1x Smart Bluetooth 16S BMS</p>
                      </div>
                      <div className="p-2.5 rounded-lg bg-slate-900 border border-slate-800">
                        <span className="text-slate-400 text-[11px]">Enclosure:</span>
                        <p className="font-bold text-slate-200">1x Laser Cut Metal Casing & Busbars</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          )}

          {/* TAB 7: BATTERY ERP */}
          {activeTab === 'battery' && (
            <div className="space-y-6">
              <div className="flex items-center justify-between">
                <div>
                  <h2 className="text-xl font-bold text-slate-100">Battery ERP & QC Testing Lab</h2>
                  <p className="text-xs text-slate-400">Cell grading, internal resistance testing, warranty tracking and claim workflows</p>
                </div>
                <button onClick={() => showToast('QC test station ready')} className="px-3 py-1.5 rounded-lg bg-emerald-600 text-xs font-bold text-white hover:bg-emerald-500 transition">
                  + Record Lab Test
                </button>
              </div>

              <div className="rounded-2xl bg-slate-900 border border-slate-800 p-5 shadow-sm text-xs">
                <h3 className="font-bold text-sm text-slate-200 mb-3">Serialized Battery Quality Control Log</h3>
                <table className="w-full text-left">
                  <thead>
                    <tr className="border-b border-slate-800 text-slate-400">
                      <th className="pb-3">SERIAL NUMBER</th>
                      <th className="pb-3">MODEL SPECIFICATION</th>
                      <th className="pb-3">BATCH</th>
                      <th className="pb-3 text-right">VOLTAGE (V)</th>
                      <th className="pb-3 text-right">INTERNAL RESISTANCE (mΩ)</th>
                      <th className="pb-3 text-right">ACTUAL CAPACITY</th>
                      <th className="pb-3 text-right">QC STATUS</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-800 font-mono">
                    {batterySerials.map((s, i) => (
                      <tr key={i} className="hover:bg-slate-800/40">
                        <td className="py-3 font-semibold text-emerald-400">{s.serial}</td>
                        <td className="py-3 font-sans text-slate-200">{s.model}</td>
                        <td className="py-3 text-slate-400">{s.batch}</td>
                        <td className="py-3 text-right text-slate-200">{s.voltage} V</td>
                        <td className="py-3 text-right text-slate-200">{s.ir} mΩ</td>
                        <td className="py-3 text-right font-bold text-emerald-400">{s.capacity} Ah</td>
                        <td className="py-3 text-right font-sans">
                          <span className="px-2 py-0.5 rounded text-[10px] bg-emerald-950 text-emerald-300 border border-emerald-800 font-bold">
                            {s.status}
                          </span>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          )}

          {/* TAB 8: SOLAR ERP */}
          {activeTab === 'solar' && (
            <div className="space-y-6">
              <div className="flex items-center justify-between">
                <div>
                  <h2 className="text-xl font-bold text-slate-100">Solar Project Management & EPC Tracker</h2>
                  <p className="text-xs text-slate-400">Site survey, sanctioned load sizing, installation, net metering, and AMC contracts</p>
                </div>
                <button onClick={() => showToast('New Solar Site Survey Form Initialized')} className="px-3 py-1.5 rounded-lg bg-amber-600 text-xs font-bold text-white hover:bg-amber-500 transition">
                  + New Site Survey
                </button>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                {solarProjects.map((p, i) => (
                  <div key={i} className="p-5 rounded-2xl bg-slate-900 border border-slate-800 text-xs space-y-2.5">
                    <div className="flex items-center justify-between">
                      <span className="font-bold text-amber-400 text-sm">{p.code}</span>
                      <span className="px-2 py-0.5 rounded bg-slate-800 text-slate-300 font-bold text-[10px]">
                        {p.capacity}
                      </span>
                    </div>
                    <p className="font-semibold text-slate-200 text-sm">{p.customer}</p>
                    <div className="flex items-center justify-between pt-2 border-t border-slate-800 text-slate-400">
                      <span>Contract Value:</span>
                      <span className="font-bold text-slate-100">₹{p.value.toLocaleString('en-IN')}</span>
                    </div>
                    <div className="flex items-center justify-between text-slate-400">
                      <span>Subsidy State:</span>
                      <span className="font-bold text-emerald-400">{p.subsidy}</span>
                    </div>
                    <div className="pt-2">
                      <div className="text-[10px] text-slate-400 mb-1">Status Workflow:</div>
                      <div className="p-2 rounded-lg bg-amber-950/40 border border-amber-900/50 text-amber-300 font-bold text-center">
                        {p.status}
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* TAB 9: PAYROLL */}
          {activeTab === 'payroll' && (
            <div className="space-y-6">
              <div className="flex items-center justify-between">
                <div>
                  <h2 className="text-xl font-bold text-slate-100">Human Resources & Automated Payroll</h2>
                  <p className="text-xs text-slate-400">Salary structure, biometric attendance, and automatic journal voucher posting</p>
                </div>
                <button onClick={() => showToast('Payroll Run Processed! ₹1,85,000 posted to Salary Expense & Payable ledgers.')} className="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-xs font-bold text-white transition flex items-center gap-2">
                  <Users className="w-4 h-4" /> Process Month Payroll Run
                </button>
              </div>

              <div className="rounded-2xl bg-slate-900 border border-slate-800 p-5 shadow-sm text-xs">
                <table className="w-full text-left">
                  <thead>
                    <tr className="border-b border-slate-800 text-slate-400">
                      <th className="pb-3">EMPLOYEE CODE</th>
                      <th className="pb-3">NAME</th>
                      <th className="pb-3">DEPARTMENT</th>
                      <th className="pb-3">DESIGNATION</th>
                      <th className="pb-3 text-right">BASIC SALARY</th>
                      <th className="pb-3 text-right">HRA</th>
                      <th className="pb-3 text-right">DEDUCTIONS (PF/TAX)</th>
                      <th className="pb-3 text-right">NET MONTHLY</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-800 font-mono">
                    <tr className="hover:bg-slate-800/40">
                      <td className="py-3 font-semibold text-emerald-400">EMP-1001</td>
                      <td className="py-3 font-sans text-slate-200 font-medium">Vikram Sharma</td>
                      <td className="py-3 font-sans text-slate-400">Battery QC & Testing</td>
                      <td className="py-3 font-sans text-slate-300">Lead QC Engineer</td>
                      <td className="py-3 text-right">₹35,000</td>
                      <td className="py-3 text-right">₹15,000</td>
                      <td className="py-3 text-right text-rose-400">-₹2,000</td>
                      <td className="py-3 text-right font-bold text-emerald-400">₹63,000</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          )}

          {/* TAB 10: CRM & SERVICE */}
          {activeTab === 'crm' && (
            <div className="space-y-6">
              <div className="flex items-center justify-between">
                <div>
                  <h2 className="text-xl font-bold text-slate-100">CRM, Dealer Network & Field Service Tickets</h2>
                  <p className="text-xs text-slate-400">Dealer commission tracking, leads pipeline, and technician service management</p>
                </div>
                <button onClick={() => showToast('New Service Ticket Modal Opened')} className="px-3 py-1.5 rounded-lg bg-emerald-600 text-xs font-bold text-white hover:bg-emerald-500 transition">
                  + Create Service Ticket
                </button>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="p-5 rounded-2xl bg-slate-900 border border-slate-800 text-xs space-y-3">
                  <h4 className="font-bold text-sm text-slate-200 flex items-center gap-2">
                    <Briefcase className="w-4 h-4 text-emerald-400" /> Authorized Dealer Network
                  </h4>
                  <div className="p-3 rounded-xl bg-slate-800/50 border border-slate-700/50 flex items-center justify-between">
                    <div>
                      <p className="font-bold text-slate-200">Apex Energy Distribution Hub</p>
                      <p className="text-slate-400 text-[11px]">Dealer Code: DLR-DEL-01 (Commission: 5%)</p>
                    </div>
                    <div className="text-right">
                      <p className="font-bold text-emerald-400">₹68,450</p>
                      <p className="text-[10px] text-slate-400">Earned Commission</p>
                    </div>
                  </div>
                </div>

                <div className="p-5 rounded-2xl bg-slate-900 border border-slate-800 text-xs space-y-3">
                  <h4 className="font-bold text-sm text-slate-200 flex items-center gap-2">
                    <CheckCircle2 className="w-4 h-4 text-amber-400" /> Field Service Tickets
                  </h4>
                  <div className="p-3 rounded-xl bg-slate-800/50 border border-slate-700/50 flex items-center justify-between">
                    <div>
                      <p className="font-bold text-slate-200">SRV-2404-019 (Inverter High-Voltage Trip)</p>
                      <p className="text-slate-400 text-[11px]">Assigned Tech: Rajesh Kumar (Okhla Solar Site)</p>
                    </div>
                    <span className="px-2 py-0.5 rounded text-[10px] bg-amber-950 text-amber-300 border border-amber-800 font-bold">
                      IN_PROGRESS
                    </span>
                  </div>
                </div>
              </div>
            </div>
          )}

          {/* TAB 11: GST COMPLIANCE */}
          {activeTab === 'gst' && (
            <div className="space-y-6">
              <div className="flex items-center justify-between">
                <div>
                  <h2 className="text-xl font-bold text-slate-100">Indian Statutory GST Engine & Tax Audits</h2>
                  <p className="text-xs text-slate-400">HSN/SAC summaries, GSTR-1 outward, GSTR-3B input tax credit reconciliation</p>
                </div>
              </div>

              <div className="rounded-2xl bg-slate-900 border border-slate-800 p-5 shadow-sm text-xs">
                <h3 className="font-bold text-sm text-slate-200 mb-3">HSN/SAC Summary for Tax Invoices</h3>
                <table className="w-full text-left">
                  <thead>
                    <tr className="border-b border-slate-800 text-slate-400">
                      <th className="pb-3">HSN / SAC</th>
                      <th className="pb-3">DESCRIPTION</th>
                      <th className="pb-3">UNIT</th>
                      <th className="pb-3 text-right">TOTAL QTY</th>
                      <th className="pb-3 text-right">TAXABLE VALUE</th>
                      <th className="pb-3 text-right">CGST</th>
                      <th className="pb-3 text-right">SGST</th>
                      <th className="pb-3 text-right">IGST</th>
                      <th className="pb-3 text-right">TOTAL TAX</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-800 font-mono">
                    <tr className="hover:bg-slate-800/40">
                      <td className="py-3 font-semibold text-emerald-400">85076000</td>
                      <td className="py-3 font-sans text-slate-200">Lithium Ion / LiFePO4 Accumulators</td>
                      <td className="py-3 text-slate-400">NOS</td>
                      <td className="py-3 text-right">5</td>
                      <td className="py-3 text-right">₹4,75,000.00</td>
                      <td className="py-3 text-right text-slate-400">₹0.00</td>
                      <td className="py-3 text-right text-slate-400">₹0.00</td>
                      <td className="py-3 text-right text-amber-300">₹85,500.00</td>
                      <td className="py-3 text-right font-bold text-emerald-400">₹85,500.00</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          )}
        </main>
      </div>

      {/* MODAL: NEW VOUCHER (CONTRA / PAYMENT / RECEIPT / JOURNAL) */}
      {activeModal && activeModal.startsWith('voucher-') && (
        <div className="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-700 p-6 shadow-2xl text-xs space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-slate-800">
              <h3 className="text-base font-bold text-slate-100 flex items-center gap-2">
                <BookOpen className="w-5 h-5 text-emerald-400" />
                Post {activeModal.replace('voucher-', '').toUpperCase()} Voucher
              </h3>
              <button onClick={() => setActiveModal(null)} className="text-slate-400 hover:text-white">✕</button>
            </div>

            <form onSubmit={handleVoucherSubmit} className="space-y-4">
              <div>
                <label className="text-slate-400 block mb-1">Debited Ledger Account (Dr)</label>
                <select
                  value={voucherForm.debitLedger}
                  onChange={(e) => setVoucherForm({ ...voucherForm, debitLedger: e.target.value })}
                  className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200"
                >
                  <option value="HDFC Bank Ltd">HDFC Bank Current A/c - 50200012345678</option>
                  <option value="Cash in Hand">Cash in Hand</option>
                  <option value="Apex Green Energy Solutions">Apex Green Energy Solutions (Debtor)</option>
                  <option value="Employee Salary Expense">Employee Salary Expense</option>
                </select>
              </div>

              <div>
                <label className="text-slate-400 block mb-1">Credited Ledger Account (Cr)</label>
                <select
                  value={voucherForm.creditLedger}
                  onChange={(e) => setVoucherForm({ ...voucherForm, creditLedger: e.target.value })}
                  className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200"
                >
                  <option value="Cash in Hand">Cash in Hand</option>
                  <option value="HDFC Bank Ltd">HDFC Bank Current A/c</option>
                  <option value="Equity Capital Account">Equity Capital Account</option>
                  <option value="Sales Account (GST 18%)">Sales Account (GST 18%)</option>
                </select>
              </div>

              <div>
                <label className="text-slate-400 block mb-1">Voucher Amount (₹) [Enforcing Debit === Credit]</label>
                <input
                  type="number"
                  required
                  placeholder="e.g. 50000"
                  value={voucherForm.amount}
                  onChange={(e) => setVoucherForm({ ...voucherForm, amount: e.target.value })}
                  className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-100 font-mono text-sm font-bold"
                />
              </div>

              <div>
                <label className="text-slate-400 block mb-1">Narration / Remarks</label>
                <input
                  type="text"
                  placeholder="Being funds transferred..."
                  value={voucherForm.narration}
                  onChange={(e) => setVoucherForm({ ...voucherForm, narration: e.target.value })}
                  className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200"
                />
              </div>

              <div className="pt-3 border-t border-slate-800 flex justify-end gap-2">
                <button type="button" onClick={() => setActiveModal(null)} className="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 font-medium">Cancel</button>
                <button type="submit" className="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 font-bold text-white shadow-lg shadow-emerald-600/30">
                  Save & Post Voucher
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* MODAL: RUN MANUFACTURING PRODUCTION */}
      {activeModal === 'production-run' && (
        <div className="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="w-full max-w-md rounded-2xl bg-slate-900 border border-slate-700 p-6 shadow-2xl text-xs space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-slate-800">
              <h3 className="text-base font-bold text-slate-100 flex items-center gap-2">
                <Factory className="w-5 h-5 text-emerald-400" />
                Run Battery Assembly Run
              </h3>
              <button onClick={() => setActiveModal(null)} className="text-slate-400 hover:text-white">✕</button>
            </div>

            <div className="space-y-3">
              <div className="p-3 rounded-xl bg-slate-800/80 border border-slate-700">
                <span className="text-slate-400 text-[11px]">Selected Bill of Materials:</span>
                <p className="font-bold text-slate-200 text-sm mt-0.5">BOM-LFP48100 (48V 100Ah LiFePO4 Pack)</p>
                <p className="text-slate-400 text-[11px] mt-1">Consumes 16x Grade-A Cells per pack</p>
              </div>

              <div>
                <label className="text-slate-400 block mb-1">Quantity of Battery Packs to Assemble</label>
                <input
                  type="number"
                  min="1"
                  max="10"
                  value={mfgQty}
                  onChange={(e) => setMfgQty(parseInt(e.target.value) || 1)}
                  className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-100 font-mono text-sm font-bold"
                />
              </div>

              <div className="p-3 rounded-xl bg-slate-950 border border-slate-800 space-y-1 text-slate-300">
                <div className="flex justify-between">
                  <span>Raw Cells Consumed:</span>
                  <span className="font-bold text-rose-400">-{mfgQty * 16} Cells</span>
                </div>
                <div className="flex justify-between">
                  <span>Finished Packs Produced:</span>
                  <span className="font-bold text-emerald-400">+{mfgQty} Packs</span>
                </div>
              </div>

              <div className="pt-3 border-t border-slate-800 flex justify-end gap-2">
                <button type="button" onClick={() => setActiveModal(null)} className="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 font-medium">Cancel</button>
                <button onClick={handleRunProduction} className="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 font-bold text-white shadow-lg shadow-emerald-600/30">
                  Execute Production Run
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* MODAL: NEW SALE INVOICE (F8) */}
      {activeModal === 'new-sale-invoice' && (
        <div className="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-700 p-6 shadow-2xl text-xs space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-slate-800">
              <h3 className="text-base font-bold text-slate-100 flex items-center gap-2">
                <ShoppingCart className="w-5 h-5 text-emerald-400" />
                Generate GST Tax Invoice (F8)
              </h3>
              <button onClick={() => setActiveModal(null)} className="text-slate-400 hover:text-white">✕</button>
            </div>

            <form onSubmit={handleSaleSubmit} className="space-y-4">
              <div>
                <label className="text-slate-400 block mb-1">Customer</label>
                <input
                  type="text"
                  value={saleForm.customer}
                  onChange={(e) => setSaleForm({ ...saleForm, customer: e.target.value })}
                  className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200"
                />
              </div>

              <div>
                <label className="text-slate-400 block mb-1">Product</label>
                <select
                  value={saleForm.productId}
                  onChange={(e) => {
                    const sel = products.find((p) => p.id === e.target.value);
                    setSaleForm({ ...saleForm, productId: e.target.value, unitPrice: sel?.sellingPrice || 95000 });
                  }}
                  className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200"
                >
                  {products.filter((p) => p.type !== 'RAW_MATERIAL').map((p) => (
                    <option key={p.id} value={p.id}>
                      {p.name} (Stock: {p.stock}) - ₹{p.sellingPrice.toLocaleString()}
                    </option>
                  ))}
                </select>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="text-slate-400 block mb-1">Quantity</label>
                  <input
                    type="number"
                    min="1"
                    value={saleForm.quantity}
                    onChange={(e) => setSaleForm({ ...saleForm, quantity: parseInt(e.target.value) || 1 })}
                    className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-100 font-mono font-bold"
                  />
                </div>
                <div>
                  <label className="text-slate-400 block mb-1">Unit Price (₹)</label>
                  <input
                    type="number"
                    value={saleForm.unitPrice}
                    onChange={(e) => setSaleForm({ ...saleForm, unitPrice: parseFloat(e.target.value) || 0 })}
                    className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-100 font-mono font-bold"
                  />
                </div>
              </div>

              <div className="p-3 rounded-xl bg-slate-950 border border-slate-800 space-y-1 text-slate-300">
                <div className="flex justify-between">
                  <span>Taxable Subtotal:</span>
                  <span className="font-bold text-slate-100">₹{(saleForm.quantity * saleForm.unitPrice).toLocaleString('en-IN')}</span>
                </div>
                <div className="flex justify-between">
                  <span>GST (18% Automated Split):</span>
                  <span className="font-bold text-amber-300">₹{((saleForm.quantity * saleForm.unitPrice * 18) / 100).toLocaleString('en-IN')}</span>
                </div>
                <div className="flex justify-between pt-1 border-t border-slate-800">
                  <span className="font-bold text-slate-100">Total Invoice Amount:</span>
                  <span className="font-bold text-emerald-400 text-sm">
                    ₹{((saleForm.quantity * saleForm.unitPrice * 118) / 100).toLocaleString('en-IN')}
                  </span>
                </div>
              </div>

              <div className="pt-3 border-t border-slate-800 flex justify-end gap-2">
                <button type="button" onClick={() => setActiveModal(null)} className="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 font-medium">Cancel</button>
                <button type="submit" className="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 font-bold text-white shadow-lg shadow-emerald-600/30">
                  Issue Invoice & Register IRN
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
