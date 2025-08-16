import React, { useState } from "react";
import { Link } from "react-router-dom";
import { Button } from "./ui/button";

export interface GoalTreeNode {
    id: number;
    title: string;
    status: string;
    description?: string;
    category?: string;
    created_at: string;
    target_date?: string;
    children?: GoalTreeNode[];
}

interface TreeViewProps {
    node: GoalTreeNode;
    level?: number;
    selectedId?: number;
}

const getStatusIndicator = (status: string): { color: string; symbol: string; bgColor: string } => {
    switch (status) {
        case "COMPLETE":
            return { color: "text-green-600", symbol: "✓", bgColor: "bg-green-50 border-green-200" };
        case "ACTIVE":
            return { color: "text-blue-600", symbol: "●", bgColor: "bg-blue-50 border-blue-200" };
        case "OPEN":
            return { color: "text-gray-500", symbol: "○", bgColor: "bg-gray-50 border-gray-200" };
        case "SKIPPED":
            return { color: "text-yellow-600", symbol: "⏸", bgColor: "bg-yellow-50 border-yellow-200" };
        default:
            return { color: "text-gray-500", symbol: "○", bgColor: "bg-gray-50 border-gray-200" };
    }
};

export const TreeView: React.FC<TreeViewProps> = ({
    node,
    level = 0,
    selectedId,
}) => {
    const [expanded, setExpanded] = useState<boolean>(true);
    const isSelected = node.id === selectedId;
    const hasChildren = node.children && node.children.length > 0;
    const statusInfo = getStatusIndicator(node.status);

    
    // Use progressive indentation that doesn't get too wide
    const indentAmount = Math.min(level * 16, 80); // Cap at 80px to prevent excessive nesting
    const lineStyle = level > 0 ? { 
        borderLeft: `2px solid ${level % 2 === 1 ? '#e5e7eb' : '#f3f4f6'}`,
        paddingLeft: '12px',
        marginLeft: `${Math.min(level * 12, 60)}px` // Capped indentation
    } : {};

    return (
        <div className="mb-1">
            <div
                style={lineStyle}
                className={`flex items-center py-2 px-3 rounded-md border transition-all duration-200 hover:shadow-sm ${
                    isSelected
                        ? "bg-indigo-50 border-indigo-200 shadow-sm"
                        : `${statusInfo.bgColor} hover:bg-opacity-80`
                }`}
            >
                {/* Expandable indicator and status symbol */}
                <div className="flex items-center space-x-2 min-w-0">
                    {hasChildren ? (
                        <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => setExpanded((e) => !e)}
                            className="p-0 h-5 w-5 hover:bg-gray-200 transition-colors"
                            tabIndex={-1}
                        >
                            <span className="text-xs text-gray-600">
                                {expanded ? "▾" : "▸"}
                            </span>
                        </Button>
                    ) : (
                        <div className="w-5" />
                    )}
                    
                    {/* Status indicator */}
                    <span className={`text-sm font-medium ${statusInfo.color}`}>
                        {statusInfo.symbol}
                    </span>
                </div>

                {/* Goal content */}
                <div className="flex-1 min-w-0 ml-3">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center space-x-2 min-w-0">
                            <Link
                                to={`/goals/${node.id}`}
                                className={`font-medium truncate hover:text-blue-600 transition-colors ${
                                    isSelected
                                        ? "text-indigo-900"
                                        : "text-gray-900"
                                }`}
                                title={node.title}
                            >
                                {node.title}
                            </Link>
                            {hasChildren && (
                                <span className="text-xs text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded-full">
                                    {node.children!.length}
                                </span>
                            )}
                        </div>
                        
                        <div className="flex items-center space-x-2">
                            {/* Target date if available */}
                            {node.target_date && (
                                <span className="text-xs text-gray-500 whitespace-nowrap">
                                    {new Date(node.target_date).toLocaleDateString()}
                                </span>
                            )}
                        </div>
                    </div>
                    
                    {/* Description on separate line if present */}
                    {node.description && (
                        <p className="text-xs text-gray-600 mt-1 line-clamp-1" title={node.description}>
                            {node.description}
                        </p>
                    )}
                </div>
            </div>
            
            
            {/* Children */}
            {hasChildren && expanded && (
                <div className="mt-1 space-y-1">
                    {node.children!.map((child) => (
                        <TreeView
                            key={child.id}
                            node={child}
                            level={level + 1}
                            selectedId={selectedId}
                        />
                    ))}
                </div>
            )}
        </div>
    );
};

export default TreeView;
