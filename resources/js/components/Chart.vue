<template>
    <div>
        <div class="float-right">
            <button v-for="button in buttons" type="button" class="btn ml-2" :class="buttonClass(button)" @click="update(button)">{{button}}</button>
        </div>

        <div class="wrapper">
            <canvas id="chart"></canvas>
        </div>
    </div>

</template>
<script>
    import * as moment from 'moment'
    import Chart from 'chart.js'

    export default {
        props: ['records'],
        data() {
            return {
                currentButton: 'Всё время',
                sale: null,
                original: null,
                chart: null

            }
        },
        computed: {
            buttons() {
                return ['Всё время', 'Год', 'Месяц']
            }
        },
        methods: {
            buttonClass(button) {
                return this.currentButton === button ? 'btn-dark' : 'btn-outline-dark'
            },
            update(button) {
                if(button === this.currentButton) return
                this.currentButton = button

                let momentFilter

                if (button === 'Год'){
                    momentFilter = moment().subtract(1, 'years')
                } else if (button === 'Месяц'){
                    momentFilter = moment().subtract(1, 'months')
                }

                let sale = this.sale
                let original = this.original

                if(typeof momentFilter !== 'undefined') {
                    sale = this.sale.filter(item => {
                        return item.x > momentFilter
                    })

                    original = this.original.filter(item => {
                        return item.x > momentFilter
                    })

                }

                this.chart.config.data.datasets[0].data = sale
                this.chart.config.data.datasets[1].data = original

                this.chart.options.scales.yAxes = [{
                    ticks: {
                        beginAtZero: button !== 'Месяц'
                    }
                }]

                this.chart.update()
            }
        },
        mounted() {
            this.sale = this.records.map(item => {
                    return {
                        x: moment(item.created_at),
                        y: parseFloat(item.sale.replaceAll(' ', ''))
                    }
                }
            )

            this.original = this.records.map(item => {
                    return {
                        x: moment(item.created_at),
                        y: parseFloat(item.original.replaceAll(' ', ''))
                    }
                }
            )

            const ctx = document.getElementById('chart')

            this.chart = new Chart(ctx, {
                type: 'line',
                data: {
                    datasets: [
                        {
                            label: 'С учетом скидок',
                            data: this.sale,
                            borderColor: '#0074D9',
                            backgroundColor: 'rgba(0, 116, 217, 0.2)'
                        },
                        {
                            label: 'Полная стоимость',
                            data: this.original
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    title: {
                        display: true,
                        text: 'Всего (руб.)'
                    },
                    tooltips: {
                        mode: 'index',
                        intersect: false
                    },
                    elements: {
                        point: {
                            radius: 0
                        }
                    },
                    scales: {
                        xAxes: [
                            {
                                type: 'time'
                            }
                        ],
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    }
                }
            })
        }
    }
</script>
<style scoped>
    .wrapper {
        height: 50vh;
    }
</style>
