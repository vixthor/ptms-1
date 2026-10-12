<style>
    @page {
        size: A4 portrait;
        margin: 10mm;
    }
    .rc-sheet {
        width: 190mm;
        max-width: 100%;
        margin: 0 auto;
        font-family: 'Tahoma', 'DejaVu Sans', sans-serif;
        font-size: 11px;
        color: #000066;
        background: #fff;
        box-sizing: border-box;
    }
    .rc-table { border-collapse: collapse; }
    .rc-w-full { width: 100%; }
    .rc-header-cell { border: 1px solid #999; padding: 6px; }
    .rc-cell { border: 1px solid #999; padding: 6px; vertical-align: top; }
    .rc-label { color: #444; }
    .rc-value { color: #28156F; font-weight: bold; }
    .rc-subjects { margin-top: 8px; }
    .rc-subjects th, .rc-subjects td { border: 1px solid #ccc; padding: 4px 6px; }
    .rc-thead-row th { background: #1DC7EA; color: #fff; font-weight: bold; }
    .rc-row-alt { background: #F7F7F8; }
    .rc-behavioural th, .rc-behavioural td { border: 1px solid #999; padding: 3px 6px; }
    .rc-comment { color: #28156F; font-style: italic; margin-top: 4px; }
    .rc-legend { font-size: 9px; color: #333; }
    .rc-legend-swatch { display: inline-block; width: 14px; height: 9px; vertical-align: middle; }
    .rc-legend-student { background: #2255cc; }
    .rc-legend-class { background: #7b3fae; }
    .rc-empty-note { text-align: center; padding: 24px; color: #777; }

    .rc-chart-container {
        border: 1px solid #999;
        padding: 10px;
        margin-top: 8px;
        height: 150px;
        overflow: hidden;
    }
    .rc-chart-plot-wrap {
        position: relative;
        margin-left: 24px;
    }
    .rc-chart-plot {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 110px;
        border-left: 1px solid #999;
        border-bottom: 1px solid #999;
    }
    .rc-chart-gridline {
        position: absolute;
        left: 0;
        right: 0;
        border-top: 1px solid #bbb;
    }
    .rc-chart-axis-tick {
        position: absolute;
        left: -24px;
        width: 20px;
        margin-top: -4px;
        font-size: 7px;
        line-height: 1;
        color: #666;
        text-align: right;
    }
    .rc-chart-table { border-collapse: collapse; }
    .rc-chart-group-cell {
        width: 60px;
        height: 110px;
        text-align: center;
        vertical-align: bottom;
    }
    .rc-chart-bars {
        height: 110px;
        font-size: 0;
        line-height: 0;
    }
    .rc-bar {
        display: inline-block;
        vertical-align: bottom;
        width: 20px;
        margin: 0;
    }
    .rc-bar-student { background: #2255cc; }
    .rc-bar-class { background: #7b3fae; }
    .rc-chart-label-cell {
        width: 60px;
        font-size: 8px;
        color: #000066;
        text-align: center;
        vertical-align: top;
        padding-top: 4px;
    }
</style>
