import type { Metadata } from 'next';
import './globals.css';

export const metadata: Metadata = {
  title: 'FUZURRA ERP — Enterprise Accounting, Battery, Solar & Manufacturing',
  description: 'Enterprise-grade Indian Accounting + ERP + Inventory + Manufacturing + Battery + Solar + Payroll + CRM + Dealer + Service Management System',
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en" className="dark">
      <body className="antialiased selection:bg-emerald-500 selection:text-white">
        {children}
      </body>
    </html>
  );
}
