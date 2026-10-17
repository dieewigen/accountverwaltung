/* DE ACP – Spielerzahl im Verlauf (spielerzahl.php): Punktdiagramm der Tageswerte, eine Reihe.
   Die Daten stehen als JSON in #spielerzahl-daten ([["JJJJ-MM-TT", Anzahl], ...], aufsteigend nach Tag).
   Fadenkreuz und Tooltip springen zum nächsten Tag (Maus, Finger, Pfeiltasten); bei Größenänderung wird neu
   gezeichnet. Liegen die Tage dichter als 8 px, werden die Punkte kleiner und verlieren ihren Ring, sonst
   entstünde ein gesprenkeltes Band. Kein externes Skript. */
(function () {
    'use strict';

    var SVGNS = 'http://www.w3.org/2000/svg';
    var TAG = 86400000;
    var MONATE = ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'];
    var HOEHE = 340;
    var RAND = { oben: 28, rechts: 16, unten: 30 };

    function knoten(name, attribute, eltern) {
        var n = document.createElementNS(SVGNS, name);
        Object.keys(attribute).forEach(function (k) {
            n.setAttribute(k, attribute[k]);
        });
        if (eltern) {
            eltern.appendChild(n);
        }
        return n;
    }

    function zahl(n) {
        return n.toLocaleString('de-DE');
    }

    function tagText(t) {
        return new Date(t).toLocaleDateString('de-DE', { weekday: 'short', day: '2-digit', month: '2-digit', year: 'numeric', timeZone: 'UTC' });
    }

    function kurzDatum(t) {
        return new Date(t).toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric', timeZone: 'UTC' });
    }

    // runde Schrittweite für die y-Achse (1, 2 oder 5 mal 10 hoch n), mindestens 1, weil es Zählwerte sind
    function yStufe(max, ziel) {
        if (max <= ziel) {
            return 1;
        }
        var roh = max / ziel;
        var p = Math.pow(10, Math.floor(Math.log10(roh)));
        var f = roh / p;
        return (f <= 1 ? 1 : f <= 2 ? 2 : f <= 5 ? 5 : 10) * p;
    }

    // Ticks an Monatsanfängen: Abstand 1, 2, 3 oder 6 Monate, bei langen Zeiträumen 1, 2, 5 oder 10 Jahre
    function xTicks(t0, t1, breite) {
        var ziel = Math.max(2, Math.floor(breite / 90));
        var a = new Date(t0);
        var b = new Date(t1);
        var i0 = a.getUTCFullYear() * 12 + a.getUTCMonth();
        var i1 = b.getUTCFullYear() * 12 + b.getUTCMonth();
        var stufen = [1, 2, 3, 6, 12, 24, 60, 120];
        var n = stufen[stufen.length - 1];
        for (var s = 0; s < stufen.length; s++) {
            if ((i1 - i0) / stufen[s] <= ziel) {
                n = stufen[s];
                break;
            }
        }
        if (Date.UTC(Math.floor(i0 / 12), i0 % 12, 1) < t0) {
            i0++;
        }
        var ticks = [];
        for (var i = Math.ceil(i0 / n) * n; ; i += n) {
            var t = Date.UTC(Math.floor(i / 12), i % 12, 1);
            if (t > t1) {
                break;
            }
            ticks.push({ t: t, text: n >= 12 ? String(Math.floor(i / 12)) : MONATE[i % 12] + ' ' + Math.floor(i / 12) });
        }
        // kein Monatsanfang im Zeitraum: Anfang und Ende beschriften
        if (ticks.length === 0) {
            ticks.push({ t: t0, text: kurzDatum(t0) });
            if (t1 > t0) {
                ticks.push({ t: t1, text: kurzDatum(t1) });
            }
        }
        return ticks;
    }

    function diagramm(figur, daten) {
        var punkte = daten.map(function (z) {
            var teile = String(z[0]).split('-');
            return { t: Date.UTC(+teile[0], +teile[1] - 1, +teile[2]), n: +z[1] };
        });
        var reihe = figur.getAttribute('data-reihe') || 'Wert';
        var letzteBreite = 0;
        var svg = null;

        // Tooltip: Wert vorn und kräftig, darunter Strichschlüssel, Reihe und Tag
        var tipp = document.createElement('div');
        tipp.className = 'chart-tipp';
        tipp.hidden = true;
        var tippWert = document.createElement('strong');
        var tippText = document.createElement('span');
        var schluessel = document.createElement('i');
        tippText.appendChild(schluessel);
        tippText.appendChild(document.createTextNode(''));
        tipp.appendChild(tippWert);
        tipp.appendChild(tippText);
        figur.appendChild(tipp);

        // Ansage für Screenreader bei Tastaturbedienung
        var ansage = document.createElement('div');
        ansage.className = 'sr-only';
        ansage.setAttribute('aria-live', 'polite');
        figur.appendChild(ansage);

        function zeichnen() {
            var breite = Math.round(figur.clientWidth);
            if (breite < 50 || breite === letzteBreite) {
                return;
            }
            letzteBreite = breite;
            if (svg) {
                svg.remove();
            }

            var t0 = punkte[0].t;
            var t1 = punkte[punkte.length - 1].t;
            if (t1 === t0) {
                t0 -= TAG;
                t1 += TAG;
            }
            var max = 0;
            var iMax = 0;
            punkte.forEach(function (p, i) {
                if (p.n >= max) {
                    max = p.n;
                    iMax = i;
                }
            });
            var stufe = yStufe(max, 5);
            var yMax = Math.max(stufe, Math.ceil(max / stufe) * stufe);
            var iLetzt = punkte.length - 1;
            var endText = zahl(punkte[iLetzt].n);
            var links = 14 + 7.5 * zahl(yMax).length;
            // rechts Platz für den Wert am Ende der Reihe
            var rechts = RAND.rechts + 10 + 8 * endText.length;
            var x0 = links + 6;
            var x1 = breite - rechts;
            var y0 = RAND.oben;
            var y1 = HOEHE - RAND.unten;
            var auswahl = -1;

            function x(t) {
                return x0 + (t - t0) / (t1 - t0) * (x1 - x0);
            }

            function y(n) {
                return y1 - n / yMax * (y1 - y0);
            }

            svg = knoten('svg', {
                width: breite,
                height: HOEHE,
                viewBox: '0 0 ' + breite + ' ' + HOEHE,
                role: 'img',
                tabindex: '0',
                'aria-label': 'Punktdiagramm ' + reihe + ' je Tag vom ' + kurzDatum(punkte[0].t) + ' bis ' + kurzDatum(punkte[iLetzt].t)
                    + ', zuletzt ' + zahl(punkte[iLetzt].n) + '. Mit den Pfeiltasten von Tag zu Tag springen.'
            });
            figur.insertBefore(svg, tipp);

            // Gitter und Achsen: Haarlinien, Beschriftung in Textfarbe
            var gitter = knoten('g', { 'class': 'gitter' }, svg);
            for (var v = 0; v <= yMax; v += stufe) {
                var yy = Math.round(y(v)) + 0.5;
                knoten('line', { x1: links, x2: breite - RAND.rechts, y1: yy, y2: yy }, gitter);
                knoten('text', { x: links - 8, y: yy, 'text-anchor': 'end', 'dominant-baseline': 'middle' }, svg).textContent = zahl(v);
            }
            xTicks(t0, t1, x1 - x0).forEach(function (tick) {
                var xx = Math.round(x(tick.t)) + 0.5;
                knoten('line', { x1: xx, x2: xx, y1: y1, y2: y1 + 4 }, gitter);
                var rechtsBuendig = xx > breite - 40;
                knoten('text', { x: rechtsBuendig ? breite - 2 : xx, y: y1 + 18, 'text-anchor': rechtsBuendig ? 'end' : 'middle' }, svg).textContent = tick.text;
            });

            // Punkte: 8 px mit 2 px Ring in der Flächenfarbe, bei dichten Tagen kleiner und ohne Ring
            var abstand = (x1 - x0) / Math.max(1, (t1 - t0) / TAG);
            var dicht = abstand < 8;
            var r = dicht ? 2.5 : 5;
            var html = '';
            punkte.forEach(function (p) {
                html += '<circle cx="' + x(p.t).toFixed(1) + '" cy="' + y(p.n).toFixed(1) + '" r="' + r + '"></circle>';
            });
            knoten('g', { 'class': 'punkte' + (dicht ? ' dicht' : '') }, svg).innerHTML = html;

            // sparsame Beschriftung: der Wert am Ende der Reihe rechts neben dem letzten Punkt, dazu der Höchstwert
            // über seinem Punkt, wenn er nicht der letzte Tag ist
            knoten('text', { x: x(punkte[iLetzt].t) + 10, y: y(punkte[iLetzt].n), 'dominant-baseline': 'middle', 'class': 'marke' }, svg).textContent = endText;
            if (iMax !== iLetzt) {
                var mx = x(punkte[iMax].t);
                var my = y(punkte[iMax].n);
                var anker = mx > x1 - 60 ? 'end' : (mx < x0 + 60 ? 'start' : 'middle');
                knoten('text', { x: mx, y: my - 12 < 12 ? my + 22 : my - 12, 'text-anchor': anker, 'class': 'marke' }, svg).textContent = 'Höchstwert ' + zahl(max);
            }

            // Fadenkreuz mit hervorgehobenem Punkt, darüber eine unsichtbare Fläche für Maus und Finger
            var kreuz = knoten('g', { 'class': 'fadenkreuz', visibility: 'hidden' }, svg);
            var linie = knoten('line', { y1: y0 - 8, y2: y1 }, kreuz);
            var punkt = knoten('circle', { r: 6 }, kreuz);
            var flaeche = knoten('rect', { x: links, y: 0, width: breite - links, height: HOEHE, fill: 'transparent' }, svg);

            function naechster(t) {
                var lo = 0;
                var hi = punkte.length - 1;
                while (hi - lo > 1) {
                    var mitte = (lo + hi) >> 1;
                    if (punkte[mitte].t < t) {
                        lo = mitte;
                    } else {
                        hi = mitte;
                    }
                }
                return Math.abs(punkte[hi].t - t) < Math.abs(punkte[lo].t - t) ? hi : lo;
            }

            function zeigen(i, ansagen) {
                auswahl = i;
                if (i < 0) {
                    kreuz.setAttribute('visibility', 'hidden');
                    tipp.hidden = true;
                    return;
                }
                var p = punkte[i];
                var px = x(p.t);
                var py = y(p.n);
                var xx = Math.round(px) + 0.5;
                linie.setAttribute('x1', xx);
                linie.setAttribute('x2', xx);
                punkt.setAttribute('cx', px);
                punkt.setAttribute('cy', py);
                kreuz.setAttribute('visibility', 'visible');

                tippWert.textContent = zahl(p.n);
                tippText.lastChild.textContent = reihe + ' · ' + tagText(p.t);
                tipp.hidden = false;
                var bw = tipp.offsetWidth;
                var bh = tipp.offsetHeight;
                var lx = px + 14;
                if (lx + bw > breite) {
                    lx = px - 14 - bw;
                }
                var ly = py - bh - 12;
                if (ly < 0) {
                    ly = py + 14;
                }
                tipp.style.left = Math.max(0, lx) + 'px';
                tipp.style.top = ly + 'px';
                if (ansagen) {
                    ansage.textContent = zahl(p.n) + ' ' + reihe + ' am ' + tagText(p.t);
                }
            }

            function zeiger(e) {
                var rahmen = svg.getBoundingClientRect();
                var t = t0 + (e.clientX - rahmen.left - x0) / (x1 - x0) * (t1 - t0);
                zeigen(naechster(t), false);
            }

            flaeche.addEventListener('pointermove', zeiger);
            flaeche.addEventListener('pointerdown', zeiger);
            flaeche.addEventListener('pointerleave', function () {
                if (document.activeElement !== svg) {
                    zeigen(-1);
                }
            });
            svg.addEventListener('focus', function () {
                zeigen(auswahl >= 0 ? auswahl : iLetzt, true);
            });
            svg.addEventListener('blur', function () {
                zeigen(-1);
            });
            svg.addEventListener('keydown', function (e) {
                var i = auswahl < 0 ? iLetzt : auswahl;
                var neu;
                switch (e.key) {
                case 'ArrowLeft':
                    neu = i - 1;
                    break;
                case 'ArrowRight':
                    neu = i + 1;
                    break;
                case 'PageUp':
                    neu = i - 30;
                    break;
                case 'PageDown':
                    neu = i + 30;
                    break;
                case 'Home':
                    neu = 0;
                    break;
                case 'End':
                    neu = iLetzt;
                    break;
                case 'Escape':
                    zeigen(-1);
                    return;
                default:
                    return;
                }
                e.preventDefault();
                zeigen(Math.max(0, Math.min(iLetzt, neu)), true);
            });
        }

        zeichnen();
        if (window.ResizeObserver) {
            new ResizeObserver(zeichnen).observe(figur);
        } else {
            window.addEventListener('resize', zeichnen);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        var figur = document.getElementById('spielerzahl-chart');
        var quelle = document.getElementById('spielerzahl-daten');
        if (!figur || !quelle) {
            return;
        }
        var daten;
        try {
            daten = JSON.parse(quelle.textContent);
        } catch (e) {
            return;
        }
        if (Array.isArray(daten) && daten.length > 0) {
            diagramm(figur, daten);
        }
    });
}());
