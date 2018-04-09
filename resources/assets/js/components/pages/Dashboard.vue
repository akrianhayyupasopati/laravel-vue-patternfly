<template>
<div>
    <h1>Laravel 5 – Our Cool Dashboard</h1>
    <div class="cards-pf">
        <div class="row row-cards-pf">
            <div class="col-md-6 col-lg-4">
            <pf-card title="System Resources" foot-note="Footer" :filter="{
                    position: 'header',
                    filters: [
                    {label: 'Filter 1'},
                    {label: 'Filter 2'},
                    ]
                }">
                    <pf-c3-chart type="donut" title="Memory" :data="{
                    columns: [
                        ['Used', 123],
                        ['Available', 74]
                    ],
                    groups: [
                        ['used', 'available']
                    ]
                    }"></pf-c3-chart>
                </pf-card>
            </div>
            <div class="col-md-6 col-lg-8">
                <pf-card title="System Resources">
                    <pf-utilization-bar-chart title='RAM Usage' units='MB' :value="8" :total="24"></pf-utilization-bar-chart>

                    <label class="label-title">Inline Layouts with Error, Warning, and Ok Thresholds</label>
                    <pf-utilization-bar-chart title='Disk I/O' units='I/Ops' :value="450" :total="500" inline :warning="60" :error="85"></pf-utilization-bar-chart>
                    <pf-utilization-bar-chart title='CPU Usage' units='MHz' :value="420" :total="500" inline :warning="60" :error="85"></pf-utilization-bar-chart>
                    <pf-utilization-bar-chart title='Memory' units='GBs' :value="25" :total="100" inline :warning="60" :error="85"></pf-utilization-bar-chart>

                    <pf-utilization-bar-chart></pf-utilization-bar-chart>
                </pf-card>
            </div>
        </div>
    </div>
</div>
</template>
<style>
body {
  background: none;
}
</style>