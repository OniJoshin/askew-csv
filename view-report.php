<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Askews CRM Report</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

    <script src="https://cdn.jsdelivr.net/npm/html-docx-js/dist/html-docx.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/file-saver"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas"></script>


    <style>
        body { padding: 2rem; background-color: #f8f9fa; }
        h2 { margin-top: 2rem; }
        .chart-container { width: 100%; max-width: 1200px; margin: auto; }
    </style>
</head>
<body>
    
    <div class="container">
        <h1 class="text-center">Askews CRM Report</h1>
        <div class="text-center my-3 no-export">
            <button class="btn btn-primary" id="exportDocx">Download Word Report</button>
        </div>
        

        <h2>Leads Overview</h2>
        <ul>
            <li>Customers: <span id="customer">171</span></li>
            <li>Qualified Leads: <span id="qualified">17</span></li>
            <li>Contact with Opportunity: <span id="contactWithOpp">23</span></li>
            <li>Contacts: <span id="contact">41</span></li>
            <li>Open Leads: <span id="open">66</span></li>
            <li>Unqualified/Closed Leads: <span id="unqualified">207</span></li>
        </ul>
        <p>
            *Qualified leads = quotes have been sent to these leads<br>
            Contact with Opportunity + open leads = these leads are still open<br>
            Contacts = these leads have not been quoted or they have not accepted the quote
        </p>
        <div class="chart-container"><canvas id="leadsChart"></canvas></div>
        <p class="mt-4" id="leadSourceSummary"></p>

        <p><br clear="all" style="page-break-before: always;"/></p>
        <h2>Enquiries by Department</h2>
        <ul id="departmentList"></ul>
        <div class="chart-container">
            <canvas id="departmentChart"></canvas>
        </div>

        <p><br clear="all" style="page-break-before: always;"/></p>
        <div class="alert alert-info mt-3">
            <p class="text-muted">CRM development plans: 
                <ul>
                    <li>CRM Training session available for any team member that requires more assistance;</li>
                    <li>Debt-Claims is now a separate CRM account; All workflows and communications between the CRM and DC Portal have been re-created. All the contacts have been imported on the new account; The DC contacts have been deleted from the Askews CRM account in the upcoming months, while we monitor and make sure that everything is working well in the new DC CRM.</li>
                    <li>Reviews email and workflow has been created for Debt-Claims customers.</li>
                    <li>ABL / CRM opportunity integration - this is ongoing. Arvi is looking at implementing this with EBY assistance; Once Arvi has managed to create and match the fields to the CRM opportunities' ones, we will look at all the automations to clear out any that are not relevant anymore.</li>
                    <li>GDPR emails for Smalleys and CL leads has been created.</li>
                    <li>Smalleys and CL ALB integration - EBY will work with Arvi to connect the systems and migrate data.</li>
                </ul>
            </p>
        </div>

        <p><br clear="all" style="page-break-before: always;"/></p>
        <h2>Email Performance</h2>
        <p>
            <strong>Askews:</strong> 
            Sent: <span id="askews-sent">0</span> |
            Delivered: <span id="askews-delivered">0</span> |
            Unique Opens: <span id="askews-opens">0</span> |
            Open Rate: <span id="askews-open-rate">0%</span> |
            Unique Clicks: <span id="askews-clicks">0</span> |
            Click Rate: <span id="askews-click-rate">0%</span>
        </p>
        <p>
            <strong>Debt-Claims:</strong> 
            Sent: <span id="debt-sent">0</span> |
            Delivered: <span id="debt-delivered">0</span> |
            Unique Opens: <span id="debt-opens">0</span> |
            Open Rate: <span id="debt-open-rate">0%</span> |
            Unique Clicks: <span id="debt-clicks">0</span> |
            Click Rate: <span id="debt-click-rate">0%</span>
        </p>

        <h3>Askews</h3>
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="askewsEmailTable">
                <thead class="table-dark">
                    <tr>
                        <th>Email Name</th>
                        <th>Email Subject</th>
                        <th>Open</th>
                        <th>Bounce</th>
                        <th>Click</th>
                        <th>Unsubscribed</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <h3>Debt-Claims</h3>
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="debtClaimsEmailTable">
                <thead class="table-dark">
                    <tr>
                        <th>Email Name</th>
                        <th>Email Subject</th>
                        <th>Open</th>
                        <th>Bounce</th>
                        <th>Click</th>
                        <th>Unsubscribed</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <p><br clear="all" style="page-break-before: always;"/></p>
        <h2>Closed Lost - Prospect's Feedback</h2>
        <ul id="feedbackList"></ul>
        <div class="chart-container">
            <canvas id="feedbackChart"></canvas>
        </div>
    </div>

    <script>
        function getParam(name) {
            return new URLSearchParams(window.location.search).get(name);
        }

        const jsonFile = getParam('data');
        if (!jsonFile) {
            document.body.innerHTML = '<div class="alert alert-danger">No data file specified.</div>';
            throw new Error("Missing data parameter.");
        }

        fetch('data/' + jsonFile)
            .then(res => res.json())
            .then(data => renderCharts(data))
            .catch(err => {
                document.body.innerHTML = '<div class="alert alert-danger">Failed to load report data.</div>';
                console.error(err);
            });
        function parseUKDateTime(str) {
            const match = str.match(/^(\d{2})\/(\d{2})\/(\d{4})/);
            if (!match) return null;
            const [_, day, month, year] = match;
            return `${year}-${month}-${day}`; // ISO-like
        }


        function renderCharts(data) {
            // --- Stacked Leads Chart ---
            const dailyStatus = {};
            const statusCounts = {};

            const fixedFeedbackOptions = [
                "The cost of our services",
                "The range of services we offer",
                "The promptness and quality of our communication",
                "Testimonials and references from other clients"
            ];

            const feedbackCounts = {};
            fixedFeedbackOptions.forEach(option => feedbackCounts[option] = 0);


            const sourceCounts = {};

            // Group leads by date and status
            data.leads.forEach(row => {
                const rawDate = row['Lead Create Date'];
                const status = row['Lead Status'] || 'Unknown';
                const feedback = row['Closed Lost - Prospect feedback']?.trim();
                if (feedback) {
                    feedbackCounts[feedback] = (feedbackCounts[feedback] || 0) + 1;
                }

                const source = row['Original Lead Source']?.trim() || 'Unknown';
                sourceCounts[source] = (sourceCounts[source] || 0) + 1;

                const isoDate = parseUKDateTime(rawDate);
                if (!isoDate) return;

                const dateKey = new Date(isoDate).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });


                // Track daily breakdown
                if (!dailyStatus[dateKey]) dailyStatus[dateKey] = {};
                dailyStatus[dateKey][status] = (dailyStatus[dateKey][status] || 0) + 1;

                // Track global totals
                statusCounts[status] = (statusCounts[status] || 0) + 1;
            });

            const summaryParts = [];
            if (sourceCounts['MoneyPenny Lead']) {
                summaryParts.push(`${sourceCounts['MoneyPenny Lead']} leads from MoneyPenny (with valid email addresses)`);
            }
            if (sourceCounts['Website Form']) {
                summaryParts.push(`${sourceCounts['Website Form']} from the website form`);
            }
            if (sourceCounts['Smalleys Website Form']) {
                summaryParts.push(`${sourceCounts['Smalleys Website Form']} leads came from Smalleys`);
            }
            if (sourceCounts['Cocks Lloyd Website']) {
                summaryParts.push(`${sourceCounts['Cocks Lloyd Website']} from Cocks Lloyd - website forms`);
            }
            if (sourceCounts['Perfect Portal']) {
                summaryParts.push(`${sourceCounts['Perfect Portal']} from Perfect Portal`);
            }

            const knownSources = ['MoneyPenny', 'Website', 'Smalleys', 'Cocks Lloyd', 'Perfect Portal'];
            const knownTotal = knownSources.reduce((sum, src) => sum + (sourceCounts[src] || 0), 0);
            const totalLeads = data.leads.length;
            const albCount = totalLeads - knownTotal;

            if (albCount > 0) {
                summaryParts.push(`The rest of the contacts (${albCount}) have been added later via the ALB integration`);
            }

            // Join and populate the paragraph
            document.getElementById('leadSourceSummary').textContent = "We have received " + summaryParts.join(", ") + ".";


            // Get all statuses for consistent stacking
            const allStatuses = Array.from(new Set(
                data.leads.map(l => l['Lead Status'] || 'Unknown')
            )).sort();

            const allDates = Object.keys(dailyStatus).sort((a, b) => new Date(a) - new Date(b));

            

            // Generate datasets for each status
            const statusColors = {
                'unqualified': 'rgba(0, 161, 0, 0.8)',
                'contact': 'rgba(23, 162, 243, 0.8)',
                'contactWithOpp': 'rgba(156, 230, 255, 0.8)',
                'customer': 'rgba(60, 212, 129, 0.8)',
                'qualified': 'rgba(255, 238, 0, 0.8)',
                'open': 'rgba(255,102,0,0.8)',
                'unknown': 'rgba(160, 160, 160, 0.8)'
            };

            const datasets = allStatuses.map(status => ({
                label: status,
                backgroundColor: statusColors[status] || 'rgba(100,100,100,0.6)',
                data: allDates.map(date => dailyStatus[date]?.[status] || 0),
                stack: 'leads',
                categoryPercentage: 0.7, // wider space between categories
                barPercentage: 0.8       // narrower bars
            }));

            const totals = allDates.map(date => {
                const statusMap = dailyStatus[date];
                return allStatuses.reduce((sum, status) => sum + (statusMap?.[status] || 0), 0);
            });

            const maxTotal = Math.max(...totals);

            document.getElementById('customer').textContent = statusCounts['customer'] || 0;
            document.getElementById('qualified').textContent = statusCounts['qualified'] || 0;
            document.getElementById('contactWithOpp').textContent = statusCounts['contactWithOpp'] || 0;
            document.getElementById('contact').textContent = statusCounts['contact'] || 0;
            document.getElementById('open').textContent = statusCounts['open'] || 0;
            document.getElementById('unqualified').textContent = statusCounts['unqualified'] || 0;


            new Chart(document.getElementById('leadsChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: allDates,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'right' },
                        datalabels: {
                            display: (ctx) => ctx.datasetIndex === datasets.length - 1,
                            formatter: (value, context) => {
                                const index = context.dataIndex;
                                return totals[index];
                            },
                            color: '#000',
                            anchor: 'end',
                            align: 'start',
                            offset: -20,
                            font: {
                                weight: 'bold',
                                size: 12
                            }
                        }
                    },
                    scales: {
                        x: { stacked: true },
                        y: { stacked: true, title: { display: true, text: 'Contacts' }, suggestedMax: Math.ceil(maxTotal * 1.05) }
                    }
                },
                plugins: [ChartDataLabels]
            });

            // --- Email Performance Tables ---
            function populateEmailTable(tableId, emails) {
                const tbody = document.querySelector(`#${tableId} tbody`);
                tbody.innerHTML = '';

                emails.forEach(email => {
                    const row = document.createElement('tr');

                    row.innerHTML = `
                        <td>${email['emailName'] || ''}</td>
                        <td>${email['emailSubject'] || ''}</td>
                        <td>${email['uniqueOpens'] || 0}</td>
                        <td>${email['bounce'] || 0}</td>
                        <td>${email['uniqueClicks'] || 0}</td>
                        <td>${email['unsubscr'] || 0}</td>
                    `;

                    tbody.appendChild(row);
                });
            }

            if (data.emails_askews) {
                populateEmailTable('askewsEmailTable', data.emails_askews);
            }
            if (data.emails_debtclaims) {
                populateEmailTable('debtClaimsEmailTable', data.emails_debtclaims);
            }

            function updateEmailStats(idPrefix, emails) {
                const sent = emails.reduce((sum, e) => sum + (parseInt(e.recipients) || 0), 0);
                const delivered = emails.reduce((sum, e) => sum + (parseInt(e.deliveries) || 0), 0);
                const opens = emails.reduce((sum, e) => sum + (parseInt(e.uniqueOpens) || 0), 0);
                const clicks = emails.reduce((sum, e) => sum + (parseInt(e.uniqueClicks) || 0), 0);

                const openRate = sent ? ((opens / delivered) * 100).toFixed(1) + '%' : '0%';
                const clickRate = sent ? ((clicks / delivered) * 100).toFixed(1) + '%' : '0%';

                document.getElementById(`${idPrefix}-sent`).textContent = sent;
                document.getElementById(`${idPrefix}-delivered`).textContent = delivered;
                document.getElementById(`${idPrefix}-opens`).textContent = opens;
                document.getElementById(`${idPrefix}-clicks`).textContent = clicks;
                document.getElementById(`${idPrefix}-open-rate`).textContent = openRate;
                document.getElementById(`${idPrefix}-click-rate`).textContent = clickRate;
            }


            if (data.emails_askews) {
                updateEmailStats('askews', data.emails_askews);
                populateEmailTable('askewsEmailTable', data.emails_askews);
            }
            if (data.emails_debtclaims) {
                updateEmailStats('debt', data.emails_debtclaims);
                populateEmailTable('debtClaimsEmailTable', data.emails_debtclaims);
            }




            // --- Department Chart ---
            const departments = Object.entries(data.departmentCounts)
                .map(([name, value]) => ({ name, value }))
                .sort((a, b) => b.value - a.value); // optional: sort by count

            const deptLabels = departments.map(d => d.name);
            const deptData = departments.map(d => d.value);

            const ul = document.getElementById('departmentList');
            ul.innerHTML = '';

            Object.entries(data.departmentCounts)
                .sort((a, b) => b[1] - a[1])
                .forEach(([dept, count]) => {
                    const li = document.createElement('li');
                    li.textContent = `${dept}: ${count}`;
                    ul.appendChild(li);
                });

            new Chart(document.getElementById('departmentChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: deptLabels,
                    datasets: [{
                        label: 'Enquiries by Department',
                        data: deptData,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)'
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        datalabels: {
                            anchor: 'end',
                            align: 'right',
                            color: '#000',
                            font: {
                                weight: 'bold'
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            title: { display: true, text: 'Enquiries' }
                        },
                        y: {
                            ticks: {
                                autoSkip: false
                            }
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });

            // --- Feedback Chart ---
            const feedbackLabels = Object.keys(feedbackCounts);
            const feedbackData = Object.values(feedbackCounts);
            const feedbackMax = Math.max(...feedbackData);

            const feedbackList = document.getElementById('feedbackList');
            feedbackList.innerHTML = '';

            feedbackLabels.forEach(label => {
                const li = document.createElement('li');
                let shortLabel = '';

                // Optionally simplify label for list display
                switch (label) {
                    case "The cost of our services":
                        shortLabel = "Costs of our services";
                        break;
                    case "The range of services we offer":
                        shortLabel = "Range of services";
                        break;
                    case "The promptness and quality of our communication":
                        shortLabel = "Communication promptness/quality";
                        break;
                    case "Testimonials and references from other clients":
                        shortLabel = "Testimonials";
                        break;
                    default:
                        shortLabel = label;
                }

                li.textContent = `${shortLabel}: ${feedbackCounts[label]}`;
                feedbackList.appendChild(li);
            });

            new Chart(document.getElementById('feedbackChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: feedbackLabels,
                    datasets: [{
                        label: 'Feedback Count',
                        data: feedbackData,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)'
                    }]
                },
                options: {
                    responsive: true,
                    layout: {
                        padding: { top: 20 }
                    },
                    plugins: {
                        legend: { display: false },
                        datalabels: {
                            anchor: 'end',
                            align: 'start',
                            offset: -20,
                            color: '#000',
                            font: { weight: 'bold', size: 14 }
                        }
                    },
                    scales: {
                        x: {
                            ticks: { autoSkip: false },
                            title: { display: false }
                        },
                        y: {
                            beginAtZero: true,
                            suggestedMax: feedbackMax + 1
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });


        }
    </script>
    <script>
        document.getElementById('exportDocx').addEventListener('click', async () => {
            const container = document.querySelector('.container');
            const clone = container.cloneNode(true);

            // Remove export-only exclusions (if any)
            clone.querySelectorAll('.no-export').forEach(e => e.remove());

            // Replace chart canvases with images
            const canvases = container.querySelectorAll('canvas');
            const cloneCanvases = clone.querySelectorAll('canvas');

            for (let i = 0; i < canvases.length; i++) {
                const canvas = canvases[i];
                const cloneCanvas = cloneCanvases[i];

                const img = document.createElement('img');
                img.src = canvas.toDataURL('image/png');
                const maxWidth = 600; // target width for Word
                const scaleFactor = maxWidth / canvas.width;
                const scaledHeight = canvas.height * scaleFactor;

                img.setAttribute('width', `${maxWidth}`);
                img.setAttribute('height', `${scaledHeight}`);
                img.style.width = `${maxWidth}px`;
                img.style.height = `${scaledHeight}px`;
                img.style.marginBottom = '1rem';

                cloneCanvas.replaceWith(img);
            }

            // Replace email tables with image snapshots using html2canvas
            async function replaceTableWithImage(selector) {
                const table = container.querySelector(selector);
                const cloneTable = clone.querySelector(selector);

                if (table && cloneTable) {
                    const canvas = await html2canvas(table, { scale: 2 });
                    const img = document.createElement('img');
                    img.src = canvas.toDataURL('image/png');
                    const maxWidth = 600; // target width for Word
                    const scaleFactor = maxWidth / canvas.width;
                    const scaledHeight = canvas.height * scaleFactor;

                    img.setAttribute('width', `${maxWidth}`);
                    img.setAttribute('height', `${scaledHeight}`);
                    img.style.width = `${maxWidth}px`;
                    img.style.height = `${scaledHeight}px`;
                    img.style.margin = '1rem 0';
                    cloneTable.replaceWith(img);
                }
            }

            await replaceTableWithImage('#askewsEmailTable');
            await replaceTableWithImage('#debtClaimsEmailTable');

            // Insert Word-compatible page breaks
            function insertPageBreakAfter(selector) {
                const target = clone.querySelector(selector);
                if (target && target.parentNode) {
                    const pageBreak = document.createElement('p');
                    pageBreak.innerHTML = '<br clear="all" style="page-break-before: always;" />';
                    target.parentNode.insertBefore(pageBreak, target.nextSibling);
                }
            }

            ['#leadsChart', '#departmentChart', '#feedbackChart'].forEach(insertPageBreakAfter);

            // Export final HTML to DOCX
            const doc = htmlDocx.asBlob(`<html><body style="margin:0.5in">${clone.innerHTML}</body></html>`);
            saveAs(doc, `Askews_CRM_Report_${new Date().toISOString().slice(0,10)}.docx`);
        });
    </script>


</body>
</html>
