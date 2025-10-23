
<div class="in-constructie-page-wrapper">
  <div class="container">
    <div class="in-constructie-wrapper">
    <div class="in-constructie-content">
      
      <div class="in-constructie-icon">
        <svg width="120" height="120" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="#16f1d3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M2 17L12 22L22 17" stroke="#16f1d3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M2 12L12 17L22 12" stroke="#16f1d3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>

      
      <h1 class="in-constructie-title">În Construcție</h1>

      
      <p class="in-constructie-desc">
        Această pagină este în curs de dezvoltare.<br>
        Te vom anunța când va fi disponibilă!
      </p>

      
      <a href="<?php echo e(route('home')); ?>" class="btn-back-home">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M19 12H5M5 12L12 19M5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Înapoi la pagina principală
      </a>
    </div>
    </div>
  </div>
</div>

<style>
.in-constructie-page-wrapper {
  background-color: #151625;
  min-height: 100vh;
  width: 100%;
}

.in-constructie-wrapper {
  min-height: 60vh;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
}

.in-constructie-content {
  max-width: 600px;
  padding: 40px 20px;
}

.in-constructie-icon {
  margin-bottom: 30px;
  animation: float 3s ease-in-out infinite;
}

.in-constructie-icon svg {
  filter: drop-shadow(0 0 20px rgba(22, 241, 211, 0.5));
}

@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-15px); }
}

.in-constructie-title {
  font-size: 3rem;
  font-weight: 800;
  color: #16f1d3;
  margin-bottom: 20px;
  text-shadow: 0 0 20px rgba(22, 241, 211, 0.5);
  letter-spacing: 2px;
}

.in-constructie-desc {
  font-size: 1.2rem;
  color: rgba(255, 255, 255, 0.8);
  margin-bottom: 40px;
  line-height: 1.6;
}

.btn-back-home {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 14px 32px;
  background: linear-gradient(135deg, #16f1d3, #0eead1);
  color: #001311;
  font-weight: 700;
  border-radius: 12px;
  text-decoration: none;
  transition: all 0.3s ease;
  box-shadow: 0 0 20px rgba(22, 241, 211, 0.4);
}

.btn-back-home:hover {
  transform: translateY(-2px);
  box-shadow: 0 0 30px rgba(22, 241, 211, 0.7);
  color: #001311;
}

@media (max-width: 768px) {
  .in-constructie-title {
    font-size: 2rem;
  }

  .in-constructie-desc {
    font-size: 1rem;
  }
}
</style>

<?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views/components/in-constructie.blade.php ENDPATH**/ ?>