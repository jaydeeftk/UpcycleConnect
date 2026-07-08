<?php

function formatStatut(string $statut): string
{
    $map = [
        'a_venir'       => 'À venir',
        'en_cours'      => 'En cours',
        'termine'       => 'Terminé',
        'terminee'      => 'Terminée',
        'valide'        => 'Validé',
        'validee'       => 'Validée',
        'en_attente'    => 'En attente',
        'rejete'        => 'Rejeté',
        'rejetee'       => 'Rejetée',
        'refuse'        => 'Refusé',
        'refusee'       => 'Refusée',
        'recupere'      => 'Récupéré',
        'reserve_pro'   => 'Réservé',
        'en_stock'      => 'En stock',
        'paye'          => 'Payé',
        'payee'         => 'Payée',
        'impaye'        => 'Impayé',
        'impayee'       => 'Impayée',
        'annule'        => 'Annulé',
        'annulee'       => 'Annulée',
        'rembourse'     => 'Remboursé',
        'remboursement_en_cours' => 'Remboursement en cours',
        'approuvee'     => 'Approuvée',
        'refusee'       => 'Refusée',
        'remboursee'    => 'Remboursée',
        'echouee'       => 'Échouée',
        'mensuel'       => 'Mensuel',
        'campagne'      => 'Par campagne',
        'unique'        => 'Unique',
        'standard'      => 'Standard',
        'encombrant'    => 'Encombrant',
        'publie'        => 'Publié',
        'publiee'       => 'Publiée',
        'vendue'        => 'Vendue',
        'brouillon'     => 'Brouillon',
        'disponible'    => 'Disponible',
        'presque_plein' => 'Presque plein',
        'plein'         => 'Plein',
        'pleine'        => 'Pleine',
        'maintenance'   => 'Maintenance',
        'hors_service'  => 'Hors service',
        'actif'         => 'Actif',
        'inactif'       => 'Inactif',
        'suspendu'      => 'Suspendu',
        'resilie'       => 'Résilié',
        'expire'        => 'Expiré',
        'pause'         => 'En pause',
        'rouvert'       => 'Rouvert',
    ];
    return $map[$statut] ?? ucfirst(str_replace('_', ' ', $statut));
}

function formatDate(?string $dateStr, bool $withTime = false): string
{
    if (empty($dateStr) || str_starts_with($dateStr, '0000-00-00')) {
        return '—';
    }
    if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2}))?/', $dateStr, $m)) {
        return $dateStr;
    }
    $date = $m[3] . '/' . $m[2] . '/' . $m[1];
    if ($withTime && isset($m[4])) {
        $date .= ' à ' . $m[4] . 'h' . $m[5];
    }
    return $date;
}

function formatPrix($prix): string
{
    $p = (float) $prix;
    return $p == 0.0 ? 'Gratuit' : number_format($p, 2, ',', ' ') . ' €';
}

function formatPeriode(?string $dateDebut, ?string $dateFin, bool $withTime = false): string
{
    if (empty($dateFin) || substr((string)$dateFin, 0, 10) === substr((string)$dateDebut, 0, 10)) {
        return formatDate($dateDebut, $withTime);
    }
    return 'Du ' . formatDate($dateDebut, $withTime) . ' au ' . formatDate($dateFin);
}

function statutCouleur(string $statut): string
{
    $map = [
        'valide'        => '#22c55e',
        'validee'       => '#22c55e',
        'payee'         => '#22c55e',
        'paye'          => '#22c55e',
        'disponible'    => '#22c55e',
        'recupere'      => '#22c55e',
        'actif'         => '#22c55e',
        'vendue'        => '#22c55e',
        'a_venir'       => '#3b82f6',
        'en_cours'      => '#3b82f6',
        'publie'        => '#3b82f6',
        'publiee'       => '#3b82f6',
        'reserve_pro'   => '#3b82f6',
        'en_stock'      => '#3b82f6',
        'en_attente'    => '#f59e0b',
        'presque_plein' => '#f59e0b',
        'pause'         => '#f59e0b',
        'suspendu'      => '#f59e0b',
        'brouillon'     => '#94a3b8',
        'termine'       => '#94a3b8',
        'terminee'      => '#94a3b8',
        'inactif'       => '#94a3b8',
        'rejete'        => '#ef4444',
        'rejetee'       => '#ef4444',
        'refuse'        => '#ef4444',
        'refusee'       => '#ef4444',
        'impayee'       => '#ef4444',
        'impaye'        => '#ef4444',
        'annule'        => '#ef4444',
        'annulee'       => '#ef4444',
        'plein'         => '#ef4444',
        'pleine'        => '#ef4444',
        'maintenance'   => '#ef4444',
        'hors_service'  => '#ef4444',
    ];
    return $map[$statut] ?? '#94a3b8';
}

/**
 * Borne min pour un champ date/datetime de programmation (événement, formation,
 * atelier, dépôt) : maintenant. Cohérent avec ValiderDateProgrammation côté API.
 */
function dateProgrammationMin(bool $avecHeure = true): string
{
    return date($avecHeure ? 'Y-m-d\T00:00' : 'Y-m-d');
}

/**
 * Borne max pour un champ date/datetime de programmation : 2 ans dans le futur.
 * Cohérent avec ValiderDateProgrammation côté API.
 */
function dateProgrammationMax(bool $avecHeure = true): string
{
    return date($avecHeure ? 'Y-m-d\TH:i' : 'Y-m-d', strtotime('+2 years'));
}
