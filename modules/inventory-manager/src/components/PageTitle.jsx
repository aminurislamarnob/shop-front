/**
 * PageTitle — the dashboard page heading (matches `.storesuite-page-main-title`
 * at 20px/600), with an optional "back to parent" link for the log view.
 */
import { ChevronLeft } from 'lucide-react';

export default function PageTitle( { title, parent } ) {
	return (
		<div className="ss:mb-6">
			{ parent && (
				<button
					type="button"
					onClick={ parent.onClick }
					className="ss:mb-2 ss:inline-flex ss:items-center ss:gap-1 ss:bg-transparent ss:text-sm ss:text-muted-foreground ss:hover:text-primary"
				>
					<ChevronLeft className="ss:h-4 ss:w-4" />
					{ parent.label }
				</button>
			) }
			{ /* Rendered as a div (not <h3>) on purpose: the dashboard theme
			     styles bare heading tags with rules our scoped utilities
			     can't override, so we keep the heading role via ARIA. */ }
			<div
				role="heading"
				aria-level="2"
				className="ss:text-xl ss:font-semibold ss:text-foreground"
			>
				{ title }
			</div>
		</div>
	);
}
