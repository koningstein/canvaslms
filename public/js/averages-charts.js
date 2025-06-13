/**
 * Averages Report Charts JavaScript
 * Handles all ApexCharts rendering for the averages report
 */

class AveragesCharts {
    constructor() {
        this.chartData = null;
        this.trendData = null;
        this.charts = {};
    }

    // Initialize all charts when DOM is ready
    init(chartData, trendData = null) {
        this.chartData = chartData;
        this.trendData = trendData;

        console.log('🎯 Initializing Averages Charts...');
        console.log('Chart data:', this.chartData);
        console.log('Trend data:', this.trendData);

        // Render all charts
        this.renderStudentPerformanceChart();
        this.renderPerformanceDistributionChart();
        this.renderModulePerformanceChart();

        if (this.trendData && this.trendData.values && this.trendData.values.length > 0) {
            this.renderTrendChart();
        }
    }

    // Student Performance Horizontal Bar Chart
    renderStudentPerformanceChart() {
        if (!this.chartData.studentNames || this.chartData.studentNames.length === 0) {
            console.warn('⚠️ No student data available for chart');
            return;
        }

        const options = {
            series: [{
                name: 'Gemiddelde Percentage',
                data: this.chartData.studentPerformances || []
            }],
            chart: {
                type: 'bar',
                height: 180,
                toolbar: { show: false },
                background: '#fff',
                fontFamily: 'Inter, sans-serif'
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    distributed: true,
                    barHeight: '75%',
                    dataLabels: {
                        position: 'center'
                    }
                }
            },
            colors: this.chartData.studentColors || ['#10B981', '#F59E0B', '#EF4444'],
            dataLabels: {
                enabled: true,
                formatter: function(val) {
                    return val + '%';
                },
                style: {
                    fontSize: '12px',
                    fontWeight: 'bold',
                    colors: ['#fff']
                }
            },
            xaxis: {
                max: 100,
                min: 0,
                title: {
                    text: 'Percentage (%)',
                    style: {
                        fontSize: '12px',
                        fontWeight: 600
                    }
                },
                labels: {
                    formatter: function(val) {
                        return val + '%';
                    },
                    style: { fontSize: '11px' }
                }
            },
            yaxis: {
                categories: this.chartData.studentNames || [],
                labels: {
                    show: true,
                    maxWidth: 140,
                    style: {
                        fontSize: '11px',
                        fontWeight: '500'
                    },
                    formatter: function(val) {
                        // Kort student namen af als ze te lang zijn
                        if (val && val.length > 18) {
                            return val.substring(0, 18) + '...';
                        }
                        return val;
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: (val, opts) => {
                        const fullName = this.chartData.studentNames[opts.dataPointIndex];
                        return `${fullName}: ${val}%`;
                    }
                }
            },
            legend: { show: false },
            grid: {
                show: true,
                strokeDashArray: 2,
                borderColor: '#e2e8f0'
            }
        };

        this.renderChart('#studentPerformanceChart', options, 'Student Performance');
    }

    // Performance Distribution Donut Chart
    renderPerformanceDistributionChart() {
        if (!this.chartData.distributionValues || this.chartData.distributionValues.every(val => val === 0)) {
            console.warn('⚠️ No distribution data available for chart');
            return;
        }

        const options = {
            series: this.chartData.distributionValues || [],
            chart: {
                type: 'donut',
                height: 180,
                background: '#fff',
                fontFamily: 'Inter, sans-serif'
            },
            labels: this.chartData.distributionLabels || [],
            colors: ['#10B981', '#F59E0B', '#EF4444'],
            legend: {
                position: 'bottom',
                fontSize: '12px',
                markers: {
                    width: 10,
                    height: 10
                },
                itemMargin: {
                    horizontal: 10,
                    vertical: 5
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function(val, opts) {
                    const count = opts.w.config.series[opts.seriesIndex];
                    const percentage = val.toFixed(1);
                    return `${count}\n(${percentage}%)`;
                },
                style: {
                    fontSize: '13px',
                    fontWeight: 'bold',
                    colors: ['#fff']
                }
            },
            tooltip: {
                y: {
                    formatter: function(val, opts) {
                        const total = opts.w.config.series.reduce((a, b) => a + b, 0);
                        const percentage = ((val / total) * 100).toFixed(1);
                        return `${val} studenten (${percentage}%)`;
                    }
                }
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '60%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                fontSize: '16px',
                                fontWeight: 600,
                                formatter: function(w) {
                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                }
                            }
                        }
                    }
                }
            }
        };

        this.renderChart('#performanceDistributionChart', options, 'Performance Distribution');
    }

    // Module Performance Column Chart
    renderModulePerformanceChart() {
        if (!this.chartData.moduleNames || this.chartData.moduleNames.length === 0) {
            console.warn('⚠️ No module data available for chart');
            return;
        }

        const options = {
            series: [{
                name: 'Gemiddelde Percentage',
                data: this.chartData.modulePerformances || []
            }],
            chart: {
                type: 'column',
                height: 180,
                toolbar: { show: false },
                background: '#fff',
                fontFamily: 'Inter, sans-serif'
            },
            plotOptions: {
                bar: {
                    distributed: true,
                    columnWidth: '70%',
                    dataLabels: {
                        position: 'top'
                    },
                    borderRadius: 4
                }
            },
            colors: this.chartData.moduleColors || ['#10B981', '#F59E0B', '#EF4444'],
            dataLabels: {
                enabled: true,
                formatter: function(val) {
                    return val + '%';
                },
                offsetY: -20,
                style: {
                    fontSize: '11px',
                    fontWeight: 'bold',
                    colors: ['#374151']
                }
            },
            xaxis: {
                categories: this.chartData.moduleNames || [],
                title: {
                    text: 'Opdrachten',
                    style: {
                        fontSize: '12px',
                        fontWeight: 600
                    }
                },
                labels: {
                    style: { fontSize: '10px' },
                    rotate: -45,
                    maxHeight: 80,
                    trim: true
                }
            },
            yaxis: {
                max: 100,
                min: 0,
                title: {
                    text: 'Percentage (%)',
                    style: {
                        fontSize: '12px',
                        fontWeight: 600
                    }
                },
                labels: {
                    formatter: function(val) {
                        return val + '%';
                    },
                    style: { fontSize: '11px' }
                }
            },
            tooltip: {
                y: {
                    formatter: (val, opts) => {
                        const fullName = this.chartData.moduleNames[opts.dataPointIndex];
                        return `${fullName}: ${val}%`;
                    }
                }
            },
            legend: { show: false },
            grid: {
                show: true,
                strokeDashArray: 2,
                borderColor: '#e2e8f0'
            }
        };

        this.renderChart('#modulePerformanceChart', options, 'Module Performance');
    }

    // Trend Line Chart
    renderTrendChart() {
        if (!this.trendData || !this.trendData.values || this.trendData.values.length === 0) {
            console.warn('⚠️ No trend data available for chart');
            return;
        }

        const options = {
            series: [{
                name: 'Gemiddelde Score',
                data: this.trendData.values
            }],
            chart: {
                type: 'line',
                height: 180,
                toolbar: { show: false },
                background: '#fff',
                fontFamily: 'Inter, sans-serif'
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            colors: ['#8B5CF6'],
            markers: {
                size: 5,
                colors: ['#8B5CF6'],
                strokeColors: '#fff',
                strokeWidth: 2
            },
            dataLabels: {
                enabled: true,
                formatter: function(val) {
                    return val + '%';
                },
                style: {
                    fontSize: '10px',
                    colors: ['#374151']
                },
                offsetY: -10
            },
            xaxis: {
                categories: this.trendData.dates || [],
                title: {
                    text: 'Datum',
                    style: {
                        fontSize: '12px',
                        fontWeight: 600
                    }
                },
                labels: {
                    style: { fontSize: '11px' },
                    rotate: -45
                }
            },
            yaxis: {
                max: 100,
                min: 0,
                title: {
                    text: 'Gemiddelde (%)',
                    style: {
                        fontSize: '12px',
                        fontWeight: 600
                    }
                },
                labels: {
                    formatter: function(val) {
                        return val + '%';
                    },
                    style: { fontSize: '11px' }
                }
            },
            grid: {
                show: true,
                strokeDashArray: 2,
                borderColor: '#e2e8f0'
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + '%';
                    }
                }
            }
        };

        this.renderChart('#trendChart', options, 'Trend Analysis');
    }

    // Generic chart renderer with error handling
    renderChart(selector, options, chartName) {
        const element = document.querySelector(selector);
        if (!element) {
            console.error(`❌ Chart container not found: ${selector}`);
            return;
        }

        try {
            // Clear any existing chart
            if (this.charts[selector]) {
                this.charts[selector].destroy();
            }

            // Create new chart
            this.charts[selector] = new ApexCharts(element, options);
            this.charts[selector].render();

            console.log(`✅ ${chartName} chart rendered successfully`);
        } catch (error) {
            console.error(`❌ Error rendering ${chartName} chart:`, error);
        }
    }

    // Destroy all charts (useful for cleanup)
    destroyAll() {
        Object.values(this.charts).forEach(chart => {
            if (chart && typeof chart.destroy === 'function') {
                chart.destroy();
            }
        });
        this.charts = {};
        console.log('🧹 All charts destroyed');
    }

    // Responsive resize handler
    handleResize() {
        Object.values(this.charts).forEach(chart => {
            if (chart && typeof chart.windowResize === 'function') {
                chart.windowResize();
            }
        });
    }
}

// Export for use in other files
window.AveragesCharts = AveragesCharts;
