APP
├───Controllers
│   ├───Admin
│   │   └───DomainController.php      <-- Baru (CRUD TLD & Pantau Domain User)
│   └───DomainController.php          <-- Baru (Pencarian, Pembelian & Manajemen oleh User)
├───Models
│   ├───DomainModel.php               <-- Baru
│   └───DomainPricingModel.php        <-- Baru
└───Views
    ├───admin
    │   └───domains                   <-- Folder Baru
    │       ├───index.php             <-- Daftar semua domain aktif
    │       └───pricing.php           <-- Atur harga TLD
    └───dashboard
        └───domains                   <-- Folder Baru
            ├───index.php             <-- List domain milik user
            ├───buy.php               <-- Form pencarian & pembelian domain baru
            └───manage.php            <-- Form ubah Nameserver (NS1/NS2) & Auto-renew