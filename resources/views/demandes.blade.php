<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi des Demandes d'Actes Administratifs - ASIN</title>
    <style>
        :root {
            --primary: #1e3a8a;
            --primary-hover: #1d4ed8;
            --bg: #f8fafc;
            --surface: #ffffff;
            --text: #0f172a;
            --muted: #64748b;
            --border: #e2e8f0;
            --danger: #b91c1c;
            --danger-bg: #fef2f2;
            --danger-border: #fecaca;
            --success: #15803d;
            --warning: #b45309;
            --info: #0284c7;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg);
            color: var(--text);
            line-height: 1.5;
            padding: 2rem 1rem;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        header {
            margin-bottom: 2rem;
            text-align: center;
        }

        h1 {
            font-size: 1.75rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .subtitle {
            color: var(--muted);
            font-size: 0.95rem;
        }

        .card {
            background-color: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: flex-end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            flex: 1;
            min-width: 200px;
        }

        label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text);
        }

        input[type="text"], select {
            padding: 0.6rem 0.75rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.15s;
            background-color: #ffffff;
        }

        input[type="text"]:focus, select:focus {
            border-color: var(--primary);
        }

        button {
            padding: 0.6rem 1.25rem;
            background-color: var(--primary);
            color: #ffffff;
            border: none;
            border-radius: 6px;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.15s;
            height: 42px;
        }

        button:hover:not(:disabled) {
            background-color: var(--primary-hover);
        }

        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .alert {
            padding: 0.85rem 1rem;
            border-radius: 6px;
            margin-bottom: 1.25rem;
            font-size: 0.925rem;
            display: none;
        }

        .alert-error {
            background-color: var(--danger-bg);
            border: 1px solid var(--danger-border);
            color: var(--danger);
        }

        .alert-info {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: var(--success);
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.9rem;
        }

        th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 600;
            padding: 0.75rem 0.85rem;
            border-bottom: 2px solid var(--border);
        }

        td {
            padding: 0.75rem 0.85rem;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        tr:hover td {
            background-color: #f8fafc;
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-deposee { background-color: #e0f2fe; color: #0369a1; }
        .badge-en_cours { background-color: #fef3c7; color: #b45309; }
        .badge-validee { background-color: #dcfce7; color: #15803d; }
        .badge-rejetee { background-color: #fee2e2; color: #b91c1c; }

        .pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 1.25rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border);
        }

        .pagination-info {
            font-size: 0.875rem;
            color: var(--muted);
        }

        .empty-state {
            text-align: center;
            padding: 2.5rem 1rem;
            color: var(--muted);
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Suivi des Demandes d'Actes</h1>
            <p class="subtitle">Portail de consultation des dossiers administratifs usagers</p>
        </header>

        <section class="card">
            <form id="search-form" class="form-row">
                <div class="form-group">
                    <label for="npi">Numéro Personnel d'Identification (NPI)</label>
                    <input type="text" id="npi" name="npi" maxlength="10" placeholder="Ex: 0123456789" required autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="statut">Statut (facultatif)</label>
                    <select id="statut" name="statut">
                        <option value="">Tous les statuts</option>
                        <option value="deposee">Déposée</option>
                        <option value="en_cours">En cours</option>
                        <option value="validee">Validée</option>
                        <option value="rejetee">Rejetée</option>
                    </select>
                </div>

                <button type="submit" id="btn-search">Rechercher</button>
            </form>
        </section>

        <div id="error-box" class="alert alert-error"></div>

        <section class="card" id="results-card" style="display: none;">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type d'acte</th>
                            <th>Copies</th>
                            <th>Statut</th>
                            <th>Date de dépôt</th>
                            <th>Motif de rejet</th>
                        </tr>
                    </thead>
                    <tbody id="demandes-tbody"></tbody>
                </table>
            </div>

            <div id="empty-state" class="empty-state" style="display: none;">
                Aucune demande trouvée pour cet usager.
            </div>

            <div id="pagination-controls" class="pagination" style="display: none;">
                <button type="button" id="btn-prev" disabled>Précédent</button>
                <span id="page-info" class="pagination-info">Page 1 sur 1</span>
                <button type="button" id="btn-next" disabled>Suivant</button>
            </div>
        </section>
    </div>

    <script>
        const form = document.getElementById('search-form');
        const npiInput = document.getElementById('npi');
        const statutSelect = document.getElementById('statut');
        const errorBox = document.getElementById('error-box');
        const resultsCard = document.getElementById('results-card');
        const tbody = document.getElementById('demandes-tbody');
        const emptyState = document.getElementById('empty-state');
        const paginationControls = document.getElementById('pagination-controls');
        const pageInfo = document.getElementById('page-info');
        const btnPrev = document.getElementById('btn-prev');
        const btnNext = document.getElementById('btn-next');
        const btnSearch = document.getElementById('btn-search');

        let currentPage = 1;
        let lastPage = 1;

        const typesActesLibelles = {
            'acte_naissance': 'Acte de naissance',
            'casier_judiciaire': 'Casier judiciaire',
            'certificat_residence': 'Certificat de résidence'
        };

        function showError(message) {
            errorBox.textContent = message;
            errorBox.style.display = 'block';
        }

        function hideError() {
            errorBox.textContent = '';
            errorBox.style.display = 'none';
        }

        function formatDate(dateIso) {
            if (!dateIso) return '-';
            try {
                const d = new Date(dateIso);
                return d.toLocaleDateString('fr-FR', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            } catch (e) {
                return dateIso;
            }
        }

        function loadDemandes(page = 1) {
            const npi = npiInput.value.trim();
            const statut = statutSelect.value.trim();

            hideError();

            if (!npi) {
                showError('Veuillez renseigner un NPI valide.');
                return;
            }

            btnSearch.disabled = true;

            const params = new URLSearchParams();
            if (statut) {
                params.append('statut', statut);
            }
            params.append('page', page);
            params.append('taille', 20);

            const url = '/api/usagers/' + encodeURIComponent(npi) + '/demandes?' + params.toString();

            fetch(url, {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok) {
                    const message = data && data.erreur ? data.erreur : 'Une erreur est survenue lors de la recherche.';
                    throw new Error(message);
                }
                return data;
            })
            .then(res => {
                resultsCard.style.display = 'block';
                // Vidage sécurisé du tbody sans innerHTML
                while (tbody.firstChild) {
                    tbody.removeChild(tbody.firstChild);
                }

                const items = res.data || [];
                const meta = res.meta || { current_page: 1, last_page: 1, total: 0 };

                currentPage = meta.current_page || 1;
                lastPage = meta.last_page || 1;

                if (items.length === 0) {
                    emptyState.style.display = 'block';
                    paginationControls.style.display = 'none';
                    return;
                }

                emptyState.style.display = 'none';

                items.forEach(item => {
                    const tr = document.createElement('tr');

                    // Colonne ID
                    const tdId = document.createElement('td');
                    tdId.textContent = '#' + item.id;
                    tr.appendChild(tdId);

                    // Colonne Type d'acte
                    const tdType = document.createElement('td');
                    tdType.textContent = typesActesLibelles[item.type_acte] || item.type_acte;
                    tr.appendChild(tdType);

                    // Colonne Copies
                    const tdCopies = document.createElement('td');
                    tdCopies.textContent = item.nombre_copies;
                    tr.appendChild(tdCopies);

                    // Colonne Statut avec badge
                    const tdStatut = document.createElement('td');
                    const badge = document.createElement('span');
                    badge.className = 'badge badge-' + item.statut;
                    badge.textContent = item.statut_libelle || item.statut;
                    tdStatut.appendChild(badge);
                    tr.appendChild(tdStatut);

                    // Colonne Date de dépôt
                    const tdDate = document.createElement('td');
                    tdDate.textContent = formatDate(item.created_at);
                    tr.appendChild(tdDate);

                    // Colonne Motif de rejet
                    const tdMotif = document.createElement('td');
                    tdMotif.textContent = item.motif_rejet || '-';
                    tr.appendChild(tdMotif);

                    tbody.appendChild(tr);
                });

                // Gestion de la pagination
                if (lastPage > 1) {
                    paginationControls.style.display = 'flex';
                    pageInfo.textContent = 'Page ' + currentPage + ' sur ' + lastPage;
                    btnPrev.disabled = (currentPage <= 1);
                    btnNext.disabled = (currentPage >= lastPage);
                } else {
                    paginationControls.style.display = 'none';
                }
            })
            .catch(err => {
                resultsCard.style.display = 'none';
                showError(err.message);
            })
            .finally(() => {
                btnSearch.disabled = false;
            });
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            currentPage = 1;
            loadDemandes(1);
        });

        btnPrev.addEventListener('click', function() {
            if (currentPage > 1) {
                loadDemandes(currentPage - 1);
            }
        });

        btnNext.addEventListener('click', function() {
            if (currentPage < lastPage) {
                loadDemandes(currentPage + 1);
            }
        });
    </script>
</body>
</html>
