<div class="card">
    <div class="card-body">
        <canvas id="categorySaleChart" style="min-height: 300px; height: 300px;"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // PHP'den gelen veriyi parse et
    const salesData = <?php echo $chartData; ?>; 
    
    const ctx = document.getElementById('categorySaleChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar', // Saatlik satış için bar grafiği daha nettir
        data: {
            labels: Array.from({length: 24}, (_, i) => i + ':00'),
            datasets: [{
                label: 'Satış Adedi',
                data: salesData,
                backgroundColor: '#3498db',
                borderColor: '#2980b9',
                borderWidth: 1
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } }
            }
        }
    });
});
</script>